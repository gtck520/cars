<?php

namespace app\controller\www;

use king\lib\Response;
use app\model\Text  as TextModel;

class Text
{
    public function get(){
        Response::SendResponseJson(200, TextModel::field(['text'])->where(['id' => 1])->find());
    } 
}
