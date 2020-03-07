<?php

namespace app\controller\www;

use king\lib\Response;
use app\model\Text  as TextModel;

class Common
{
    /**
     * @OA\Get(
     *     path="/common/getHpzl",
     *     tags={"车辆查询"},
     *     summary="获取号牌种类",
     *     @OA\Response(response=200,description="OK"),
     *     @OA\Response(response=400,description="请求失败")
     * )
     */
    public function getHpzl(){
        $data=[
            "01"=>"大型汽车",
            "02"=>"小型汽车",
            "51"=>"大型新能源汽车",
            "52"=>"小型新能源汽车",
            "03"=>"使馆汽车",
            "04"=>"领馆汽车",
            "05"=>"境外汽车",
            "06"=>"外籍汽车",
            "07"=>"普通摩托车",
            "08"=>"轻便摩托车",
            "09"=>"使馆摩托车",
            "10"=>"领馆摩托车",
            "11"=>"境外摩托车",
            "12"=>"外籍摩托车",
            "13"=>"低速车",
            "14"=>"拖拉机",
            "15"=>"挂车",
            "16"=>"教练汽车",
            "17"=>"教练摩托车",
            "18"=>"试验汽车",
            "19"=>"试验摩托车",
            "20"=>"临时入境汽车",
            "21"=>"临时入境摩托车",
            "22"=>"临时行驶车",
            "23"=>"警用汽车",
            "24"=>"警用摩托",
            "25"=>"原农机号牌",
            "26"=>"香港入出境车",
            "27"=>"澳门入出境车",
        ];
        Response::sendResponseJson(200, json_encode($data,JSON_UNESCAPED_UNICODE));

    } 
}
