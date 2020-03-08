<?php

namespace app\controller\www;

use king\lib\Response;
use king\lib\Upload;

class U extends UserController
{
    public function images()
    {
        $upload = Upload::getClass();
        $url = $upload->save('', '', 1,'images');
        Response::SendResponseJson(200, C('domain').$url);
    }
}
