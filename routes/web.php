<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('test_tpl', 'UserController@sendTpl');
$router->group(['prefix' => 'api/v1', ['middleware' => ['log']]], function () use ($router) {
    
    $router->get('/', function () use ($router) {
        echo $router->app->version();
    });
    /**
     * set a wechat session
     */
    $router->post('wechat-auth/session/{code}', 'AuthController@getWechatSession');
    $router->post('upload', 'AttachmentController@uploadAttachment');
    $router->post('upload_receipt', 'AttachmentController@uploadReceipt');
    $router->post('wechat-auth/phone_number/{code}', 'AuthController@getPhoneNumber');
    $router->post('wechat-notify', 'NotifyController@sendNotify');

    /**
     * bind WeChat User to CRM
     */
    $router->post('wechat-auth/bind/{code}', 'AuthController@syncWechatToCrm');

//    $router->group(['middleware' => ['wechat-auth', 'auth']], function () use ($router) {
    //
    //        $router->post('post/{id}/like', 'PostController@like');
    //    });
});

function resource($uri, $controller)
{
    global $app;

    $app->get($uri . '/all', $controller . '@all');
    $app->post($uri, $controller . '@create');
    $app->post($uri . '/search', $controller . '@search');
    $app->get($uri . '/{id}', $controller . '@show');
    $app->put($uri . '/{id}', $controller . '@update');
    $app->patch($uri . '/{id}', $controller . '@update');
    $app->delete($uri . '/{id}', $controller . '@destroy');
}
