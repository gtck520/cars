<?php

namespace app\controller\www;


class Index
{
    //生成swagger文档并跳转至文档页面
    /**
     * @OA\Info(title="车塘小程序API", version="0.1")
     */
    public function index(){
        echo "12312344456656";exit;
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

    public function go() {
        // webhook上设置的secret
        $secret = "kangvvip";
        // 校验发送位置，正确的情况下自动拉取代码，实现自动部署
        $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        if ($signature) {
            $hash = "sha1=".hash_hmac('sha1', file_get_contents("php://input"), $secret);
            if (strcmp($signature, $hash) == 0) {
                // sign sucess

                set_time_limit(3 * 60); //最大过期时间3分钟
                $shellPath = "/www/wwwroot/ct.kanglan.vip";
                $cmd = "cd $shellPath && sudo git pull && chown -R www:www ./ && chmod -R 777 ./";
                //$cmd = "cd $shellPath && sudo git pull && sudo /bin/bash CI.sh";
                $res = $this -> doShell($cmd);
                print_r($res); // 主要打印结果给github记录查看，自己测试时查看

            }
        }
    }


    /*
     * 执行shell命令
     */
    protected function doShell ($cmd, $cwd = null) {
        $descriptorspec = array(
            0 => array("pipe", "r"), // stdin
            1 => array("pipe", "w"), // stdout
            2 => array("pipe", "w"), // stderr
        );
        $proc = proc_open($cmd, $descriptorspec, $pipes, $cwd, null);
        // $proc为false，表明命令执行失败
        if ($proc == false) {
            return false;
            // do sth with HTTP response
            print_r("命令执行出错！");
        } else {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $status = proc_close($proc); // 释放proc
        }
        $data = array(
            'stdout' => $stdout, // 标准输出
            'stderr' => $stderr, // 错误输出
            'retval' => $status, // 返回值
        );

        return $data;
    }

}
