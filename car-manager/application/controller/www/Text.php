<?php

namespace app\controller\www;

use king\lib\Response;
use app\service\Text as TextService;
use app\validate\Text as TextValidate;

class Text extends AdminController
{
    //登录
    public function get(){
        $res = TextService::get();
        Response::SendResponseJson($res['code'], $res['data']);
    }
    
     //修改文本
     public function update(){
        $req = json_decode(Put(), true);
        if (!isset($req['text'])) {
            Response::SendResponseJson(400, '文本字段未定义');
        }
        $admin_id = parent::$admin_id;
        $res = TextService::update($admin_id, $req['text']);
        Response::SendResponseJson($res['code'], $res['data']);
    }
}
