<?php

namespace app\controller\www;


class Index
{
    //生成swagger文档并跳转至文档页面
    /**
     * @OA\Info(title="车塘小程序API", version="0.1")
     */
    public function index(){
        $req=G();
        if(isset($req['type'])&&$req['type']=="admin"){

            try {
                $parentDirName = FCPATH . '../../car-manager/application/controller/www';
                $openapi = \OpenApi\scan($parentDirName);
                $jsonStr = $openapi->toJson();
                file_put_contents(FCPATH. '/swagger-ui/apijson/swagger_manager.json', $jsonStr);
                echo "生成成功！";
                $url="http://".$_SERVER['HTTP_HOST']."/swagger-ui/dist/manager.html";
                Header("Location: $url");
            } catch (\Throwable $e) {
                echo $e->__toString();
            }
        }else
        {
            try {
                $parentDirName = dirname(dirname(__FILE__));
                $openapi = \OpenApi\scan($parentDirName);
                //header('Content-Type: application/x-yaml');
                $jsonStr = $openapi->toJson();
                file_put_contents(FCPATH. '/swagger-ui/apijson/swagger.json', $jsonStr);
                echo "生成成功！";
                $url="http://".$_SERVER['HTTP_HOST']."/swagger-ui/dist/index.html";
                Header("Location: $url");
            } catch (\Throwable $e) {
                echo $e->__toString();
            }
        }


    }

}
