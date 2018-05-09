<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/24/18
 * Time: 10:37 AM
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LogMiddleware
{

    private $_start;

    private $_end;

    protected function log(Request $request, Response $response)
    {
        Log::info('=================INPUT REQUEST=================');
        Log::info('Duration:' . number_format($this->_end - $this->_start, 3));
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->getMethod());
        Log::info('IP Address: ' . $request->getClientIp());
        Log::info('Status Code: ' . $response->getStatusCode());
        Log::info('Query params: ' . json_encode($request->query(), JSON_PRETTY_PRINT));
        if($request->getMethod() === 'POST') {
            Log::info('Body: ' . json_encode($request->post(), JSON_PRETTY_PRINT));
        }
    }

    public function handle($request, Closure $next)
    {
        $this->_start = microtime(true);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $this->_end = microtime(true);

        $this->log($request, $response);
    }
}