<?php

namespace app\service;

use app\helper\Helper;
use app\model\Text as TextModel;

class Text 
{
    public static function get()
    {
        $res = TextModel::find();
        return ['code' => 200, 'data' => $res['text']];
    }

    public static function update($admin_id, $text)
    {
        $old_value = TextModel::find();
        TextModel::where(['id'=>1])->update([
            'text' => $text,
        ]);
        Helper::saveToLog($admin_id, '', $old_value['text'], $text, "管理员: $admin_id 修改了帮助文本");
        return ['code' => 200, 'data' => ''];
    }

}
