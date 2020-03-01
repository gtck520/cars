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
*/