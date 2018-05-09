<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    /**
     * http status CREATED
     * @param null $content
     * @return Response
     */
    public function Created($content = null)
    {
        return response()->json($content, 201);
    }

    public function OK($content = null)
    {
        return response()->json($content, 200);
    }

    public function BadRequest($content = null)
    {
        return response()->json($content, 400);
    }

    public function InternalError($content = null)
    {
        return response()->json($content, 500);
    }

    protected $messageFormat = [
        'between' => ':attribute 必须在:min - :max 之间.',
        'required' => ':attribute 必填项.',
        'required_without_all' => '当 :values为空时，:attribute 不为空',
        'exists' => ':attribute 不存在.'

    ];

    protected function customerValidate(Request $request, $rule)
    {
        $this->validate($request, $rule, $this->messageFormat);
    }
}
