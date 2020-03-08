<?php

namespace app\service;

use app\helper\Helper;
use app\model\UserLevel as UserLevelModel;

class UserLevel
{
    public static function get()
    {
        $res = UserLevelModel::get();
        return ['code' => 200, 'data' => $res];
    }

    public static function update($admin_id, $id,$req)
    {
        $old_value = UserLevelModel::find();
        UserLevelModel::where(['id'=>$id])->update($req);
        Helper::saveToLog($admin_id, '', json_encode($old_value), json_encode($req), "管理员: $admin_id 修改了收费规则");
        return ['code' => 200, 'data' => ''];
    }

}
