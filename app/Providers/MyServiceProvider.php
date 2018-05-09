<?php

namespace App\Providers;

/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 1:57 PM
 */
use App\Services\Account\{
    LoginContract, LoginRepository, User, UserObserver, UserContract, UserRepository
};

use App\Services\KdCrm\{
    KdCrmContract, KdCrmService
};
use App\Services\Payway\PaywayContract;
use App\Services\Payway\PaywayService;
use App\Services\VerifyCode\VerifyCodeContract;
use App\Services\VerifyCode\VerifyCodeRepository;
use App\Services\Helpers;
use Illuminate\Support\ServiceProvider;


class MyServiceProvider extends ServiceProvider
{

    public function boot()
    {
        User::observe(new UserObserver);
    }

    public function register()
    {
        $app = $this->app;


        $app->bind(LoginContract::class, LoginRepository::class);
        $app->bind(UserContract::class, UserRepository::class);
        $app->bind(VerifyCodeContract::class, VerifyCodeRepository::class);
        $app->bind(KdCrmContract::class, KdCrmService::class);
        $app->bind(PaywayContract::class, PaywayService::class);
        /*
         * for My Facade
         */
        $app->bind('helper', function () {
            return new Helpers();
        });
    }
}
