<?php

namespace app\controller\www;
//本文档存放接口的返回模型用于swagger显示

/**
 *  @OA\Schema(
 *     schema="query_getpay",
 *     @OA\Property(property="spu_id",type="integer",description="商品Spu编码",example=520),
 *     @OA\Property(property="sku_id",type="integer",description="商品SKU编码",example=14507),
 *     @OA\Property(property="goods_name",type="string",description="商品名称",example="商品名称417"),
 *     @OA\Property(property="brand_name",type="string",description="品牌-中文描述",example="品牌864"),
 *     @OA\Property(property="status",description="商品最终状态",ref="#components/schemas/query_getpay_status"),
 *     @OA\Property(property="delete_status",description="删除最终状态",ref="#components/schemas/query_getpay_delete_status"),
 *     @OA\Property(property="is_listing",type="integer",description="是否上架",example=1),
 *     @OA\Property(property="tag_remark",type="array",description="tag描述",@OA\Items(type="string",example="tag描述")),
 *     @OA\Property(property="gallery",type="array",description="相册",@OA\Items(ref="#components/schemas/query_getpay_gallery")),
 *     @OA\Property(property="category",description="",ref="#components/schemas/query_getpay_category"),
 *  ),
 *  @OA\Schema(
 *     schema="query_getpay_status",
 *     @OA\Property(property="key",type="integer",description="状态",example=1),
 *     @OA\Property(property="value",type="string",description="状态中文描述",example="上架"),
 *  ),
 *  @OA\Schema(
 *     schema="query_getpay_delete_status",
 *     @OA\Property(property="key",type="integer",description="状态",example=1),
 *     @OA\Property(property="value",type="string",description="状态中文描述",example="删除"),
 *  ),
 *  @OA\Schema(
 *     schema="query_getpay_gallery",
 *     @OA\Property(property="big",type="string",description="大图片地址",example="https://img13.360buyimg.com/n7/jfs/t1/41729/34/1969/283035/5cc80ed4Eab41eae2/dc784f471bc44ce2.jpg"),
 *     @OA\Property(property="small",type="string",description="小图片地址",example="https://img13.360buyimg.com/n5/jfs/t1/41729/34/1969/283035/5cc80ed4Eab41eae2/dc784f471bc44ce2.jpg"),
 *  ),
 *  @OA\Schema(
 *     schema="query_getpay_category",
 *     @OA\Property(property="id",type="integer",description="类目ID",example=1),
 *     @OA\Property(property="pid",type="integer",description="上级类目ID",example=2),
 *     @OA\Property(property="name",type="string",description="类目名称",example="一级类目名称"),
 *     @OA\Property(property="img_url",type="string",description="类目图片地址",example="https://img.com/2312/2434/1342/1.png"),
 *     @OA\Property(property="child",type="array",description="三级子类目",@OA\Items(ref="#components/schemas/query_getpay_category_child")),
 *  ),
 *  @OA\Schema(
 *     schema="query_getpay_category_child",
 *     @OA\Property(property="id",type="integer",description="类目ID",example=2),
 *     @OA\Property(property="pid",type="integer",description="上级类目ID",example=1),
 *     @OA\Property(property="name",type="string",description="类目名称",example="二级类目名称"),
 *     @OA\Property(property="img_url",type="string",description="类目图片地址",example="https://img.com/2312/2434/1342/1.png"),
 *     @OA\Property(property="child",type="array",description="三级子类目",@OA\Items(ref="#components/schemas/query_getpay_category_child_child")),
 *  ),
 *  @OA\Schema(
 *     schema="query_getpay_category_child_child",
 *     @OA\Property(property="id",type="integer",description="类目ID",example=3),
 *     @OA\Property(property="pid",type="integer",description="上级类目ID",example=2),
 *     @OA\Property(property="name",type="string",description="类目名称",example="三级类目名称"),
 *     @OA\Property(property="img_url",type="string",description="类目图片地址",example="https://img.com/2312/2434/1342/1.png"),
 *  ),
 *  @OA\Schema(
 *     schema="login_easylogin",
 *     @OA\Property(property="status",type="integer",description="状态",example=0),
 *     @OA\Property(property="token",type="string",description="token",example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InVzZXJfaWQiOjEwMDAwLCJvcGVuaWQiOiIxMjMxMjMxMjMiLCJzZXNzaW9uX2tleSI6IiIsInRpbWUiOjE1ODMwNTM1MjR9LCJpYXQiOjE1ODMwNTM1MjQsImV4cCI6MTU4MzEzOTkyNH0.lQmk0hbJZFTQ54VBMBLYNQ1SVTMs_FPyEzHgspgJANs"),
 *  ),

*/