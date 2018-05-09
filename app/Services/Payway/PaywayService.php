<?php


namespace App\Services\Payway;

use Carbon\Carbon;
use GuzzleHttp\Client;


/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 6/14/17
 * Time: 9:00 AM
 */
class PaywayService implements PaywayContract
{

    private $httpClient;

    function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => env('PAYWAY_API')
        ]);
    }

    private function getChinaTime($format = "Y-m-d H:i:s")
    {
        $timezone_out = date_default_timezone_get();

        date_default_timezone_set('Asia/Shanghai');
        $chinaTime = date($format);

        date_default_timezone_set($timezone_out);

        return $chinaTime;
    }

    private function _buildPwRequest($method = 'GET', $api, $formData = null, $query = null)
    {
        $time = (string)time();
        $nonce = '3dfsd3';
        $fp = 1;
        $arr = [$time, $nonce, $api];
        sort($arr);
        $str = implode('^', $arr);
        $hash = md5($str);
        $retStr = substr($hash, 0, 10) . 'Companycn' . substr($hash, 10);
        $sn = md5($retStr);

        try {
            $params = [
                'tp' => $api,
                'timestamp' => $time,
                'nonce' => $nonce,
                'sn' => $sn,
                'fp' => $fp
            ];

            $query = $query ? array_merge($query, $params) : $params;
            $resp = $this->httpClient->request($method, 'mallshop', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $formData,
                'query' => $query
            ]);

            return $resp;
        } catch (\Exception $exception) {
            throw $exception;
        }
//        $urlTpl = 'http://kdmallapipv.companycn.net/CommonAPI/mallshop?tp=' . $api . '&timestamp=' . $tp . '&nonce=' . $nonce . '&sn=' . $sn . '&fp=' . $fp;


//        return $urlTpl;
    }


    /**
     * @param int $cityId
     * @param int $pageIndex
     * @param int $pageSize
     * @return mixed
     * @throws \Exception
     */
    public function getMallList($cityId = 1, $pageIndex = 1, $pageSize = 10)
    {
        $req = $this->_buildPwRequest('POST', 'loadmalllist', [
            'city' => $cityId,
            'page' => $pageIndex,
            'pageSize' => $pageSize
        ]);

        $resp = $req
            ->getBody()
            ->getContents();


        return $resp;
    }

    public function getLocationMall()
    {
        // TODO: Implement getLocationMall() method.
    }

    /**
     * @param $mallId
     * @return mixed
     */
    public function getFloorInfo($mallId)
    {
        // TODO: Implement getFloorInfo() method.
    }

    public function getMerchantclassify()
    {
        // TODO: Implement getMerchantclassify() method.
    }

    /***
     * mall_id: number;
     * floor: number;
     * type: number;
     * sort?: number;
     * key?: string;
     * page?: number;
     * pageSize?: number;
     * @return mixed
     */
    public function searchShop($criteria)
    {
        // http://kdmallapipv.companycn.net/CommonAPI/mallshop?tp=loadshoplist&timestamp=1498199883&nonce=3dfsd3&sn=4a10ffae04fd878ac10051381947d82b&fp=1
        $req = $this->_buildPwRequest('POST', 'loadshoplist', $criteria);

        $resp = $req->getBody()->getContents();

        return $resp;
    }

    public function getNearByMall($latlng)
    {
        $time = (string)time();

        $nonce = '3dfsd3';
        $fp = 1;
        $arr = [$time, $nonce, 'getmalllist'];
        sort($arr);
        $str = implode('^', $arr);
        $hash = md5($str);
        $retStr = substr($hash, 0, 10) . 'Companycn' . substr($hash, 10);
        $sn = md5($retStr);

        try {
            $params = [
                'tp' => 'getmalllist',
                'timestamp' => $time,
                'nonce' => $nonce,
                'sn' => $sn,
                'fp' => $fp
            ];
            $endPoint = env('PAYWAY_APP_API');
            $resp = $this->httpClient->request('POST', $endPoint, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'lat' => $latlng['lat'],
                    'lng' => $latlng['lng']
                ],
                'query' => $params,
                'verify' => false
            ])
                ->getBody()
                ->getContents();

            return $resp;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function getShopDetail($shopId)
    {
        $req = $this->_buildPwRequest('POST', 'shopdetail', [
            'shop_id' => $shopId
        ]);

        return $req->getBody()->getContents();
    }
}