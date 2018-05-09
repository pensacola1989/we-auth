<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 6/14/17
 * Time: 10:26 AM
 */

namespace App\Http\Controllers;

use App\Services\Payway\PaywayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaywayController extends Controller
{
    private $paywayService;

    public function __construct(PaywayContract $contract)
    {
        $this->paywayService = $contract;
    }

    public function mallList(Request $request)
    {
        //        $mallList = $this->paywayService->getMallList();
        app()->configure('location-code-map');
        $locationConfig = config('location-code-map');

        $latlng = $request->input('latlng');
        list($lat, $lng) = explode(',', $latlng);
        $mallList = $this->paywayService->getNearByMall([
            'lat' => $lat,
            'lng' => $lng,
        ]);
        $parsed = json_decode($mallList);
        if ($parsed && $parsed->data && $parsed->data->near_mall) {
            $mallId = $parsed->data->near_mall->mall_id;
            Log::info('----------------------------------------------');
            Log::info($mallId);
            Log::info(json_encode($locationConfig));
            Log::info('----------------------------------------------');
            $parsed->data->near_mall->locationCode = $locationConfig[$mallId];
        }

        return json_encode($parsed);
    }

    public function searchShop(Request $request)
    {
        $shopList = $this->paywayService->searchShop([
            'mall_id' => $request->input('mall_id') ?? 10,
            'type' => $request->input('type') ?? 0,
            'key' => $request->input('key') ?? '',
            'floor' => $request->input('floor') ?? 0,
            'page' => $request->input('page') ?? 1,
            'pageSize' => $request->input('page_size') ?? 10,
        ]);
        dd($shopList);
    }

    public function shopDetail()
    {

    }
}
