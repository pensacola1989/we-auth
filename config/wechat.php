<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/17/17
 * Time: 11:19 PM
 */

return [
    'appid' => 'wxcefd504e4a0863cd',
    'secret' => 'f5a57cbd8ad7cb32af45c040808aa1c1',
    'code2session_url' => "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
    'send_template_msg_url' => 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=ACCESS_TOKEN',
    'access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
    'template_message_url' => 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s',
    'templates' => [
        'APPROVE_WX_TPL_ID' => [
            'id' => '0l4n6KzX90fli7EcnN3kSYua_b40-GJDAo_qzTGCBl4',
        ],
        'REJECT_WX_TPL_ID' => [
            'id' => '_HjDDGJhCDhUW9SSlwHEPbJigFFp_AWWt_bvWI558vw',
        ],
    ],
];
