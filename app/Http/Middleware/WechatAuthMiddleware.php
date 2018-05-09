<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 9:28 AM
 */

namespace App\Http\Middleware;

use App\Services\Account\UserContract;
use Closure;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class WechatAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Closure|Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $refer = $request->headers->get('referer');
        $sKey = $request->headers->get('sKey');
        if ($sKey) {
            $sessionOpenId = Cache::get($sKey);
            if (!$sessionOpenId) {
                return response('Unauthorized.', 401);
            }
            list($sessionKey, $openId, $token) = explode(',', $sessionOpenId);
            $request->headers->set('Authorization', 'Bearer ' . $token);
            $request->headers->set('openId', $openId);


        }
        else if (app()->environment('local') && !str_contains($refer, 'servicewechat')) {
            $user = app()->make(UserContract::class)->requireById(6);
            $token = JWTAuth::fromUser($user);
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}