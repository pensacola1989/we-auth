<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 10:04 PM
 */

return [
    'oss_id' => env('OSS_ID'),
    'oss_secret' => env('OSS_SECRET'),
    'oss_endpoint' => app()->environment('local') ? env('OSS_END_POINT') : env('OSS_INTERN_END_POINT')
];