<?php namespace App\Services;


use Illuminate\Http\Request;
use OSS\OssClient;
use Toin0u\Geotools\Facade\Geotools;
/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/27/2015
 * Time: 8:19 PM
 */

class Helpers {

    public function newId()
    {
        $snowFlaker = new \App\Services\Util\idwork(['workId'=>1]);
        return $snowFlaker->nextId();
    }
    /**
     * timestamp or null
     * @param $time
     * @return int
     */
    public function setTimeStamp($time)
    {
        return $time == null ? time() : intval($time);
    }

    /**
     * covert geo lat & lng string to geoHash
     * @param $geoString 121.346243,31.350845
     * @return mixed
     */
    public function convertGeoToHash($geoString)
    {
        $coordToGeohash = Geotools::coordinate($geoString);
        $encoded = Geotools::geoHash()->encode($coordToGeohash, 12);
        return $encoded->getGeoHash();
    }
    /**
     * convert hash to geo
     * @param  [type] $hash [description]
     * @return [type]       [description]
     */
    public function coverHashToGeo($hash)
    {
        $decoded = Geotools::geohash()->decode($hash);
        return [
            'lat' => $decoded->getCoordinate()->getLatitude(),
            'lng' => $decoded->getCoordinate()->getLongitude()
        ];
    }
    /**
     * 'lat & lng' -> $geoStr
     * @param  [type] $geoStr [description]
     * @return [type]         [description]
     */
    public function parseGeoFromStr($geoStr)
    {
        $list = explode(',', $geoStr);
        return [
            'lat'   =>  $list[0],
            'lng'   =>  $list[1]
        ];
    }

    function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function uploadAliOSS(string $fileName, string $path)
    {
        app()->configure('oss');
        $ossConfig = config('oss');
        $ossClient = new OssClient($ossConfig['oss_id'], $ossConfig['oss_secret'], $ossConfig['oss_endpoint']);
        $ossClient->uploadFile('ehe1989', $fileName, $path);
        unlink($path);
    }
//    public function uploadAliOSS($fileName, Request $request)
//    {
//        app()->configure('oss');
//        $ossConfig = config('oss');
//        $ossClient = new OssClient($ossConfig['oss_id'], $ossConfig['oss_secret'], $ossConfig['oss_endpoint']);
//        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
//            throw new Exception('file not specified or not valid!');
//        }
//        $file = $request->file('file');
//        $ossClient->uploadFile('ehe1989', $fileName, $file);
//        unlink($request->file('file')->getRealPath());
//    }
}