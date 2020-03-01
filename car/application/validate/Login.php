<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Login extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('code', 'required', 'code');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

}
