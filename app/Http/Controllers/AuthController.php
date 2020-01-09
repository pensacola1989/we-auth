<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/17/17 * Time: 5:51 PM
 */

namespace App\Http\Controllers;

use App\Services\Account\LoginContract;
use App\Services\Account\UserContract;
use App\Services\Exception\EntityNotFoundException;
use App\Services\KdCrm\KdCrmContract;
use App\Services\WechatAuth\WechatAuth;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    protected $jwt;
    protected $userRepository;
    protected $loginRepository;
    protected $crmRepository;

    public function __construct(
        JWTAuth $jwt,
        UserContract $userContract,
        LoginContract $loginRepo,
        KdCrmContract $crmContract
    ) {
        $this->jwt = $jwt;
        $this->userRepository = $userContract;
        $this->loginRepository = $loginRepo;
        $this->crmRepository = $crmContract;
    }

    public function getToken(Request $request)
    {
        $user = $this->userRepository->getUserByMobile($request->mobile);
        if (!$user) {
            throw new EntityNotFoundException;
        }
        $token = $this->jwt->fromUser($user);
        return response()->json(compact('token'));
    }

    public function getPhoneNumber(Request $request, $code = null)
    {
        try {
            Log::info('############INTO getPhoneNumber##############');
            $wechat = App::make(WechatAuth::class);
            $wechat->getLoginInfo($code);
            $decryptedData = $wechat->getUserInfo($request->encryptedData, $request->iv);
            return $decryptedData;
        } catch (RequestException $e) {
            Log::info(json_encode($e));
        }
    }

    public function getSessionForWeChat(Request $request, $authCode)
    {
        $status = 0;
        $weChat = app()->make(WechatAuth::class);
        $authInfo = $weChat->getLoginInfo($authCode);
        $unionId = isset($authInfo['unionid']) ? $authInfo['unionid'] : null;
        try {
            $profile = $this->crmRepository->publshProfileToken($unionId);
            $profileObj = json_decode($profile);
            $profileObj->MemberInfo = $this->crmRepository->getMemberNumber($profileObj->CustomerNumber, $profileObj->Token);
            $profileObj->Summary = $this->crmRepository->getMemberSummary($profileObj->CustomerNumber, $profileObj->Token);
            $status = 1;

            return [
                'status' => 1,
                'profile' => $profileObj,
                'unionId' => $unionId,
                'openId' => $authInfo['openid'],
            ];
        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'profile' => null,
                'unionId' => isset($authInfo['unionid']) ? $authInfo['unionid'] : null,
                'openId' => $authInfo['openid'],
            ];
        }
    }

    public function getWechatSession(Request $request, $code)
    {
        $userInfo = '';
        try {
            Log::info('------------------in to wechat session-------------');
            $wechat = App::make(WechatAuth::class);
            $loginInfo = $wechat->getLoginInfo($code);
            $userInfo = $wechat->getUserInfo($request->encryptedData, $request->iv);
            $userInfo = json_decode($userInfo);
            $unionId = $userInfo->unionId;
            Log::info('...........' . $unionId . '............');
            $profile = $this->crmRepository->publshProfileToken($unionId);
            $profileObj = json_decode($profile);
            // dd($profile);
            Log::info('.............................profile obj.......');
            Log::info($profile);
            Log::info($profileObj->Token);
            $profileToken = $profileObj->Token;
            $profileObj->MemberInfo = $this->crmRepository->getMemberNumber($profileObj->CustomerNumber, $profileToken);
            Log::info('........................finish get member info......');
            $profileObj->Summary = $this->crmRepository->getMemberSummary($profileObj->CustomerNumber, $profileToken);
            return [
                'status' => 1,
                'profile' => $profileObj,
                'unionId' => $unionId,
                'openId' => $userInfo->openId,
            ];
        } catch (RequestException $e) {
            $code = $e->getCode();
            if ($code == 404) {
                return [
                    'status' => 0,
                    'profile' => null,
                    'unionId' => $userInfo->unionId,
                    'openId' => $userInfo->openId,
                ];
            } else {
                return $e;
            }
        }
    }

    public function syncWechatToCrm(Request $request, $code)
    {
        Log::info('===================enter sync wechat to crm==================');
        $mobile = $request->mobile;
        // $verifyCode = $request->verifyCode;
        $locationCode = $request->locationCode;
        // $verifyCodeInCache = Cache::get($mobile);
        // if ($verifyCode != $verifyCodeInCache) {
        //     throw new VerifyCodeException;
        // }
        $weChat = app()->make(WechatAuth::class);
        $loginInfo = $weChat->getLoginInfo($code);
        $userInfo = $weChat->getUserInfo($request->encryptedData, $request->iv);
        $userInfo = json_decode($userInfo);
        $profile = $this->syncWechatUser($mobile, $userInfo, $locationCode);

        $profileObj = json_decode($profile);
        $profileObj->MemberInfo = $this->crmRepository->getMemberNumber($profileObj->CustomerNumber, $profileObj->Token);
        $profileObj->Summary = $this->crmRepository->getMemberSummary($profileObj->CustomerNumber, $profileObj->Token);

        return [
            'status' => 1,
            'profile' => $profileObj,
            'unionId' => $userInfo->unionId,
            'openId' => $userInfo->openId,
        ];
        // return json_encode($profileObj);

    }

    private function syncWechatUser($mobile, $wechatUserInfo, $locationCode = 'ESITE')
    {
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
        try {
            $this->crmRepository->updateSocialProfile($customerNumber, [
                'unionId' => $wechatUserInfo->unionId,
                'nickName' => $wechatUserInfo->nickName,
                'genderCode' => $wechatUserInfo->gender == 1 ? 'M' : 'F',
            ]);
        } catch (\Exception $ex) {
            Log::log('....social profile already linked...');
        } finally {
            $profile = $this->crmRepository->publshProfileToken($wechatUserInfo->unionId);

            return $profile;
        }
    }
}
