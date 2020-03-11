<?php

namespace app\controller\www;
//本文档存放接口的返回模型用于swagger显示

/**
 *  @OA\Schema(
 *     schema="login_easylogin",
 *     @OA\Property(property="status",type="integer",description="状态",example=0),
 *     @OA\Property(property="token",type="string",description="token",example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InVzZXJfaWQiOjEwMDAwLCJvcGVuaWQiOiIxMjMxMjMxMjMiLCJzZXNzaW9uX2tleSI6IiIsInRpbWUiOjE1ODMwNTM1MjR9LCJpYXQiOjE1ODMwNTM1MjQsImV4cCI6MTU4MzEzOTkyNH0.lQmk0hbJZFTQ54VBMBLYNQ1SVTMs_FPyEzHgspgJANs"),
 *  ),
 *
 *
 *  @OA\Schema(
 *     schema="query_getpay",
 *     @OA\Property(property="cost",type="string",description="本次查询需要消费金额",example=""),
 *     @OA\Property(property="money",type="string",description="当前余额",example="0.00"),
 *     @OA\Property(property="pay",type="integer",description="是否够支付，够的话为1，不够的话，本字段返回支付链接",example=1),
 *  ),
 *
 *  @OA\Schema(
 *     schema="query_report",
 *     @OA\Property(property="id",type="integer",description="",example=1),
 *     @OA\Property(property="user_id",type="integer",description="用户id",example=10002),
 *     @OA\Property(property="order_id",type="integer",description="订单号",example=15),
 *     @OA\Property(property="vin",type="string",description="查询的车架号",example="LSVFD26R1B2722145"),
 *     @OA\Property(property="maintenance",type="string",description="维保查询结果（详情字段请查阅第三方的接口文档）",example="['url'=>'https=>\\/\\/ct.kanglan.vip\\/CallBack\\/maintenance?orderid=14','content'=>['orderReportVehicleModel'=>['cxi'=>'POLO','flag'=>true,'pp'=>'大众'],'orderReportVehicleAccident'=>['result1'=>3],'orderReportVehicleRepair'=>['data'=>[['code'=>'0001','vin'=>'LSVFD26R1B2722145','repairRecordList'=>[['date'=>'2012-06-22','materal'=>'*D4机油！￥;机油滤清器;','type'=>'首次保养','content'=>'01表检查;02表检查;首次7500公里免费保养;','mileage'=>'5642']]]],'result1'=>1],'orderReportVehicle'=>['flag'=>true,'suppliers'=>[2],'vin'=>'LSVFD26R1B2722145']],'vin'=>'LSVFD26R1B2722145','orderNo'=>30963]"),
 *     @OA\Property(property="collision",type="string",description="碰撞查询结果（未查询，或者查询失败则为null或空字符。）",example=""),
 *     @OA\Property(property="vehicleCondition",type="string",description="汽车状态查询结果（未查询，或者查询失败则为null或空字符。）",example=""),
 *     @OA\Property(property="regulations",type="string",description="违章查询结果（同上）",example=""),
 *     @OA\Property(property="add_time",type="string",description="报告生成时间",example="0000-00-0000=>00=>00"),
 *  ),
 *
 *  @OA\Schema(
 *     schema="query_record",
 *     @OA\Property(property="total",type="integer",description="总记录数",example=48),
 *     @OA\Property(property="rs",type="array",description="查询记录数组",@OA\Items(ref="#components/schemas/query_record_rs")),
 *  ),
 *  @OA\Schema(
 *     schema="query_record_rs",
 *     @OA\Property(property="id",type="integer",description="订单号",example=1),
 *     @OA\Property(property="user_id",type="integer",description="用户id",example=10002),
 *     @OA\Property(property="vin",type="string",description="查询的车架号",example="LSVFD26R1B2722145"),
 *     @OA\Property(property="cost",type="string",description="",example=""),
 *     @OA\Property(property="type",type="integer",description="查询的类型",example=0),
 *     @OA\Property(property="status",type="integer",description="订单的状态",example=1),
 *     @OA\Property(property="msg",type="string",description="提示内容",example=""),
 *     @OA\Property(property="create_time",type="string",description="订单创建时间",example="2020-03-0709:40:05"),
 *     @OA\Property(property="status_msg",type="string",description="与status对照",example="查询中"),
 *     @OA\Property(property="type_msg",type="string",description="与type对照",example="维保查询"),
 *  ),

 *

 */