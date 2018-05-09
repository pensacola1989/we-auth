<?php

namespace App\Services\Payway;
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 6/14/17
 * Time: 9:00 AM
 */


interface PaywayContract
{
    /**
     * @param int $cityId
     * @param int $pageIndex
     * @param int $pageSize
     * @return mixed
     */
    public function getMallList($cityId = 1, $pageIndex = 1, $pageSize = 10);

    public function getLocationMall();

    /**
     * @param $mallId
     * @return mixed
     */
    public function getFloorInfo($mallId);

    public function getMerchantclassify();

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
    public function searchShop($criteria);

    public function getNearByMall($latlng);

    public function getShopDetail($shopId);
}