<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 5/21/17
 * Time: 10:51 PM
 */

namespace App\Services\KdCrm;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class KdCrmService implements KdCrmContract
{
    private $apiEndPoint;

    private $serviceAuth;

    private $crmToken;

    protected $httpClient;

    public function __construct()
    {
        $this->apiEndPoint = env('CRM_END_POINT');
        $this->serviceAuth = env('CRM_SVC_AUTH');
        $this->crmToken = env('CRM_TOKEN');
        $this->httpClient = new Client([
            'base_uri' => env('CRM_END_POINT'),
        ]);
    }

    private function buildRequestForCrm($method = "GET", $url, $formData = null, $query = null)
    {
        try {
            $response = $this->httpClient->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cache-Control' => 'no-cache',
                    'Token' => $this->crmToken,
                    'SvcAuth' => $this->serviceAuth,
                ],
                'form_params' => $formData,
                'query' => $query,
                'verify' => false,
            ]);

            return $response;
        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    public function bindUserToCrm($openId)
    {
        // TODO: Implement bindUserToCrm() method.
    }

    public function isUserbind($customerNumber)
    {

    }

    private function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    public function createProfile(array $profile)
    {
        return $this->buildRequestForCrm(
            'POST',
            'api/customize/create-CAPITASTAR-with-mobile',
            [
                "MobileNumber" => $profile['mobile'],
                "Name" => $profile['nick_name'] || '',
                "GenderCode" => "M",
                "Password" => '88888888',
                "Birthday" => '1990-11-24T00:00:00+08:00',
                "LocationCode" => $profile['locationCode'],
//                "LocationCode" => "ESITE"
            ]
        )
            ->getBody()
            ->getContents();
    }

    public function getCustomerNumber($mobile)
    {
        return $this->buildRequestForCrm(
            'POST',
            'api/profile/search-simple',
            [
                'MobileNumber' => $mobile,
            ]
        )
            ->getBody()
            ->getContents();
    }

    public function getOpenIdBycustomerNumber($customerNumber)
    {
        return $this->buildRequestForCrm(
            'GET',
            'api/profile/' . $customerNumber . '/social-profile'
        )
            ->getBody()
            ->getContents();
    }

    public function publshProfileToken($unionId)
    {
        return $this->buildRequestForCrm(
            'POST',
            'api/profile/social-signin',
            [
                'mediaCode' => 'WeChat',
                'identifier' => $unionId,
            ]
        )
            ->getBody()
            ->getContents();
    }

    public function deleteProfileToken($customerNumber, $mediaCode)
    {
        return $this->buildRequestForCrm(
            'DELETE',
            'api/profile/' . $customerNumber . '/social-profile/' . $mediaCode
        );
    }

    public function updateSocialProfile($customerNumber, array $socialProfile)
    {
        try {
            $response = $this->httpClient->request('POST',
                'api/profile/' . $customerNumber . '/social-profile',
                [
                    'headers' => [
//                    'Content-Type' => 'application/x-www-form-urlencoded',
                        'Cache-Control' => 'no-cache',
                        'Token' => $this->crmToken,
                        'SvcAuth' => $this->serviceAuth
                        // 'Token' => '2a1f9757-de00-4035-b820-c368c8d542fa',
                        // 'SvcAuth' => 'TUlOSUFQUC1BRE1JTg==,Q3ladjhORmhBQg==',
                    ],
                    'json' =>
                    [
                        'MediaCode' => 'WeChat',
                        'Identifier' => $socialProfile['unionId'],
                        'ProfileDetails' => [
                            'FirstName' => $socialProfile['nickName'],
                            'GenderCode' => $socialProfile['genderCode'],
                            'AcquireDateTime' => '2017-05-25T18:45:10+08:00',
                        ],
                    ],
                    'verify' => false,
                ]);

            return $response;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getMemberNumber($customerNumber, $profileToken = null)
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'api/profile/' . $customerNumber . '/memberships',
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Cache-Control' => 'no-cache',
                        'Token' => $this->crmToken,
                        'SvcAuth' => $this->serviceAuth,
                        'ProfileToken' => $profileToken,
                    ],
                    'verify' => false,
                ])
                ->getBody()
                ->getContents();

            return json_decode($response)[0];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getMemberSummary($customerNujmber, $profileToken = null)
    {
        // $response = $this->buildRequestForCrm(
        //     'GET',
        //     'api/profile/' . $customerNujmber . '/summary'
        // )
        //     ->getBody()
        //     ->getContents();

        // return json_decode($response);
        //
        Log::info('..................profile token in summary call................');
        Log::info($profileToken);
        try {
            $response = $this->httpClient->request(
                'GET',
                'api/profile/' . $customerNujmber . '/summary',
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Cache-Control' => 'no-cache',
                        'Token' => $this->crmToken,
                        'SvcAuth' => $this->serviceAuth,
                        'ProfileToken' => $profileToken,
                    ],
                    'verify' => false,
                ])
                ->getBody()
                ->getContents();

            Log::info('...................summary response..................');
            Log::info($response);
            return json_decode($response);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getTicketHistory($profileToken, $memberNumber, $maxNo = 20)
    {
        $response = $this->httpClient->request(
            'GET',
            'api/member/' . $memberNumber . '/receipts',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cache-Control' => 'no-cache',
                    'Token' => $this->crmToken,
                    'SvcAuth' => $this->serviceAuth,
                    'ProfileToken' => $profileToken,
                ],
                'maxNo' => $maxNo,
                'verify' => false,
            ]
        )
            ->getBody()
            ->getContents();
        return json_decode($response);
    }

    public function uploadReceipt()
    {
        // TODO: Implement uploadReceipt() method.
    }

    public function getReceiptImage($receiptName)
    {
        $response = $this->buildRequestForCrm(
            'GET',
            'api/receipt/' . $receiptName . '/image'
        )
            ->getBody()
            ->getContents();

        return json_decode($response);
    }

    public function signIn($customNumber, $password)
    {
        $response = $this->httpClient->request(
            'POST',
            'api/profile/' . $customNumber . '/signin',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cache-Control' => 'no-cache',
                    'Token' => $this->crmToken,
                    'SvcAuth' => $this->serviceAuth,
                    // ,
                    // 'ProfileToken' => $profileToken,
                ],
                'json' => [
                    'Password' => $password,
                ],
                'verify' => false,
            ]
        )
            ->getBody()
            ->getContents();

        return json_decode($response);
    }

    public function publishRedeem($memberNumber, $scoreType)
    {
        $response = $this->httpClient->request(
            'POST',
            'api/product/redeem-point-product',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                    'Token' => $this->crmToken,
                    'SvcAuth' => $this->serviceAuth,
                ],
                'json' => [
                    'MemberNumber' => $memberNumber,
                    'LocationCode' => 'LHQS',
                    'ProductDetails' => [
                        ['ProductCode' => $scoreType],
                    ],
                ],
                'verify' => false,
            ]
        )
            ->getBody()
            ->getContents();

        return json_decode($response);
    }
}
