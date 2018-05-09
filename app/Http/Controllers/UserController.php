<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 6:35 PM
 */

namespace App\Http\Controllers;

use App\Services\Account\UserContract;
use App\Services\KdCrm\KdCrmContract;
use App\Services\WechatAuth\WechatAuth;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;
use JiaweiXS\WeApp\WeApp;

class UserController extends Controller
{
    private $validateRule = [
        'name' => 'required_without_all:nick_name|between:1,20',
        'nick_name' => 'required_without_all:name|between:1,20',
        'mobile' => 'alpha_num',
    ];

    private $userRepository;

    private $crmRepository;

    public function __construct(UserContract $userContact, KdCrmContract $crmContract)
    {
        $this->userRepository = $userContact;
        $this->httpClient = new Client();
        $this->crmRepository = $crmContract;
    }

    private function getRandomCode()
    {
        $retCode = '';
        $len = 3;
        foreach (range(0, $len) as $number) {
            $retCode .= random_int(0, 9);
        }

        return $retCode;
    }

    public function sendSms($mobile)
    {
        $code = $this->getRandomCode();
        $expiresAt = Carbon::now()->addMinutes(10);

        Cache::put($mobile, $code, $expiresAt);
        $url = 'http://www.uoleem.com.cn/api/uoleemApi?username='
        . env('SMS_USERNAME')
        . '&pwd='
        . env('SMS_PASS')
            . '&mobile='
            . $mobile
            . '&content='
            . $code;

        try {
            return $this->httpClient
                ->request('GET', $url)
                ->getStatusCode();

        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    public function sendTpl()
    {
        $weapp = new WeApp(env('WECHAT_APP_ID'), env('WECHAT_APP_SECRET'), '../storage/cache');
        $templateMsg = $weapp->getTemplateMsg();

        $ret = $templateMsg->getList(0, 3);
        return $ret;
    }

    public function publicRedeem(Request $request)
    {
        try {
            $redeemType = $request->input('redeem_type');
            $openId = $request->input('open_id');
            $formId = $request->input('form_id');
            $userName = $request->input('nick_name');
            $memberInfo = $this->_syncWechatToCrm($request);
            Log::info(json_encode($memberInfo));
            if ($redeemType === 'q') {
                // 500
                $ret = $this->crmRepository->publishRedeem($memberInfo->MemberInfo->MemberNo, 'HQ500-180302');
                try {
                    if ($ret) {
                        $this->_sendTpl([
                            'openId' => $openId,
                            'userName' => $userName,
                        ], $formId, 500);
                    }
                    return $ret;
                } catch (\Exception $e) {
                    Log::info(json_encode($e->getMessage()));
                    return ['status' => 101];
                }
            } elseif ($redeemType === 'a') {
                $multiRedeemRet = [];
                $redeemCount = intval($request->input('right_count', 0));
                if ($redeemCount > 0) {
                    foreach (range(0, $redeemCount - 1) as $index) {
                        $multiRedeemRet[] = $this->crmRepository->publishRedeem($memberInfo->MemberInfo->MemberNo, 'HQ200-180302');
                    }

                }
                try {
                    $this->_sendTpl([
                        'openId' => $openId,
                        'userName' => $userName,
                    ], $formId, $request->input('right_count') * 200);

                    return $this->OK();
                } catch (\Exception $e) {
                    Log::info(json_encode($e->getMessage()));
                    return ['status' => 101];
                }
            }
        } catch (RequestException $e) {
            Log::info($e->getMessage());
            Log::info('....error in publish redeem....');
            return ['err' => true];
        }
    }

    private function _sendTpl($userInfo, $formId, $redeem)
    {
        $weapp = new WeApp(env('WECHAT_APP_ID'), env('WECHAT_APP_SECRET'), '../storage/cache');
        $templateMsg = $weapp->getTemplateMsg();
        $msgRet = $templateMsg->send($userInfo['openId'], env('REDEEM_WX_TPL_ID'), $formId, [
            'keyword1' => ["value" => $redeem],
            'keyword2' => ['value' => $userInfo['userName']],
            'keyword3' => ['value' => $redeem],
            'keyword4' => ['value' => '关注公众号 [ 凯德星生活圈 ]，积分当钱花'],
        ]);
        Log::info(json_encode($msgRet));
    }

    private function _syncWechatUser($mobile, $wechatUserInfo, $locationCode = 'LHQS')
    {
        try {
            $ret = $this->crmRepository->getCustomerNumber($mobile);
            $ret = json_decode($ret);
            $customerNumber = '';

            $isUserExist = count($ret) && ($customerNumber = $ret[0]->CustomerNumber);
            if (!$isUserExist) {
                // create profile
                $profileResponse = $this->crmRepository->createProfile([
                    'nick_name' => $wechatUserInfo->nickName,
                    'mobile' => $mobile,
                    'locationCode' => $locationCode,
                ]);
                $profileRet = json_decode($profileResponse);
                $profileRet && $customerNumber = $profileRet->CustomerNumber;
            }
            $this->crmRepository->updateSocialProfile($customerNumber, [
                'unionId' => $wechatUserInfo->unionId,
                'nickName' => $wechatUserInfo->nickName,
                'genderCode' => $wechatUserInfo->gender == 1 ? 'M' : 'F',
            ]);
            // }
            $profile = $this->crmRepository->publshProfileToken($wechatUserInfo->unionId);

            return $profile;
        } catch (\Exception $err) {
            Log::info($err->getMessage());
        } finally {
            $profile = $this->crmRepository->publshProfileToken($wechatUserInfo->unionId);

            return $profile;
        }

    }

    private function _syncWechatToCrm($request)
    {
        Log::info('@@@@@@ sync at redeem @@@@@@');
        $mobile = $request->input('mobile');
        $locationCode = $request->input('locationCode', 'LHQS');

        $weChat = app()->make(WechatAuth::class);
        $loginInfo = $weChat->getLoginInfo($request->input('code'));
        $userInfo = $weChat->getUserInfo($request->input('encryptedData'), $request->input('iv'));
        $userInfo = json_decode($userInfo);
        $profile = $this->_syncWechatUser($mobile, $userInfo, $locationCode);

        $profileObj = json_decode($profile);
        $profileObj->MemberInfo = $this->crmRepository->getMemberNumber($profileObj->CustomerNumber, $profileObj->Token);
        $profileObj->Summary = $this->crmRepository->getMemberSummary($profileObj->CustomerNumber, $profileObj->Token);

        return $profileObj;
    }

    public function summary($customerNo, $profileToken)
    {
        $resp = $this->crmRepository->getMemberSummary($customerNo, $profileToken);

        return json_encode($resp);
    }

    public function getReceiptImage($receiptName)
    {
        $resp = $this->crmRepository->getReceiptImage($receiptName);
        $ret = $resp->Result;
        header("Content-type: image/jpg");
        echo base64_decode($ret);
    }

    public function search(Request $request)
    {
        return $this->userRepository->search($request);
    }

    public function create(Request $request)
    {
        $this->customerValidate($request, $this->validateRule);
        $user = $this->userRepository->createUser($request->input());

        return $user;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRule);
        $user = $this->userRepository->updateUserInfo($id, $request);

        return $user;
    }

    public function all()
    {
        return $this->userRepository->getAll();
    }

    public function destroy($id)
    {
        $model = $this->userRepository->requireById($id);
        $this->userRepository->delete($model);

        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->userRepository->requireByExternalId($id);

        return $model;
    }

    public function getTicketHistory(Request $request, $memberNo, $profileToken)
    {
        $resp = $this->crmRepository->getTicketHistory($profileToken, $memberNo);

        return $resp;
    }

    public function miniQr($unionId)
    {
        Log::info($unionId);
        $bgPath = storage_path('er_bg.png');
        $img = Image::make($bgPath)->resize(750, 1334);
        $img2 = Image::make($this->_generateMiniQr($unionId))
            ->resize(150, 150);

        $img->insert($img2, 'left-bottom', 20, 50);

        return $img->response('jpg');
    }

    private function _generateMiniQr($unionId)
    {
        $weapp = new WeApp(env('WECHAT_APP_ID'), env('WECHAT_APP_SECRET'), '../storage/cache');
        $qrCode = $weapp->getQRCode();
        $res = $qrCode->getQRCodeA('pages/qa-index/qa-index?union_id=' . $unionId, $width = null, $auto_color = null, $line_color = [
            'r' => '228',
            'g' => '77',
            'b' => '132',
        ]);

        return $res;
//        return response($res, 200, ['Content-Type' => 'image/jpeg']);
    }
}
