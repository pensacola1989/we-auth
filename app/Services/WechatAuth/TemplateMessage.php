<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/23/17
 * Time: 10:50 AM
 */

namespace App\Services\WechatAuth;

use Illuminate\Support\Facades\Cache;
use Ixudra\Curl\Facades\Curl;

class TemplateMessage
{
    private $templateMessageUrl;
    private $accessTokenUrl;
    private $tplConfig;
    private $appId;
    private $secret;

    function __construct($wxConfig)
    {
        $this->templateMessageUrl = $wxConfig['template_message_url'];
        $this->tplConfig = include 'TemplateConfig.php';
        $this->accessTokenUrl = $wxConfig['access_token_url'] ?? '';
        $this->appId = $wxConfig["appid"] ?? "";
        $this->secret = $wxConfig["secret"] ?? "";
    }

    private function constructMessage($msgType, $msgBody)
    {
        $tpl = $this->tplConfig[$msgType];
        $tpl['page'] = $msgBody['page'] ?? '';
        if (count($msgBody['data']) > 0) {
            foreach ($msgBody['data'] as $data) {
                $val = $data['value'] ?? '';
                $color = $data['color'] ?? '';
                $tpl['data']['value'] = $val;
                $tpl['data']['color'] = $color;
            }
        }
        $tpl['touser'] = $msgBody['openId'];
        $tpl['form_id'] = $msgBody['formId'];

        return $tpl;
    }

    public function getAccessToken()
    {
        if ($cachedToken = Cache::get('access_token')) {
            return $cachedToken;
        }
        $accessTokenUrl = sprintf($this->accessTokenUrl, $this->appId, $this->secret);
        $json = Curl::to($accessTokenUrl)->get();
        $ret = json_decode($json, true);
        if(!isset($ret['access_token'])) {
            throw new \Exception('weixin_accesstoken_got_failed');
        }
        $this->accessToken = $ret['access_token'];
        $cacheExpiresAt = Carbon::now()->addMinutes(100);
        Cache::Put('access_token', $this->accessToken, $cacheExpiresAt);

        return $this->accessToken;
    }

    public function sendTemplateMessage($msgType, $openId, $data){
        $templateData['openId'] = $openId;
        $templateData['data'] = $data;

        $msgForSend = $this->constructMessage($msgType, $templateData);
        $msgUrl = sprintf($this->templateMessageUrl, $this->getAccessToken());
        $json = Curl::to($msgUrl)
            ->withData($msgForSend)
            ->asJson()
            ->post();
        dd($json);
    }

}