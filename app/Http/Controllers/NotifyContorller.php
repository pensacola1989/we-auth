<?php

use Illuminate\Http\Request;
use JiaweiXS\WeApp\WeApp;
use Laravel\Lumen\Routing\Controller;

class NotifyController extends Controller
{
    public function __construct()
    {

    }

    public function sendNotify(Request $request)
    {
        $weapp = new WeApp(env('WECHAT_APP_ID'), env('WECHAT_APP_SECRET'), '../storage/cache');
        $templateMsg = $weapp->getTemplateMsg();

        $msgKey = $request->input('msg_key');
        $openId = $request->input('open_id');
        $formId = $request->input('form_id');
        $data = $request->input('data');

        $cfg = config('wechat');
        $tplId = $cfg['templates'][$msgKey]['id'];

        $templateMsg->send($openId, $tplId, $formId, $data);

    }
}
