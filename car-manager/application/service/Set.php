<?php

namespace app\service;

use app\helper\Helper;
use app\model\CityActive as CityActiveModel;
use app\model\CityArea as CityAreaModel;

class Set
{
    public static function getCity($req)
    {
        $pid= $req['pid'] ?? 0;//无参数则直接读取第一级
        $res = CityAreaModel::where(['pid'=>$pid])->get();
        return ['code' => 200, 'data' => $res];
    }


}
