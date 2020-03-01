<?php

namespace king\lib;

use king\core\Instance;

class Lang extends Instance
{
    public static function get($attr, $lang = '')
    {
        $lang_locate = C('lang');
        if (empty($lang_locate)) {
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $lang_locate = strtolower($matches[1]);
            } else {
                $lang_locate = 'zh-cn';
            }
        }

        $sys_lang_file = SYS_PATH . 'lib/lang/' . $lang_locate . EXT;
        if (is_file($sys_lang_file)) {
            $langs = require $sys_lang_file;
        } else {
            $langs = require SYS_PATH . 'lib/lang/zh-cn' . EXT;
        }

        $lang_file = APP_PATH . 'lang/' . $lang_locate . EXT;
        $langs_custom = [];
        if (is_file($lang_file)) {
            $langs_custom = require $lang_file;
        }

        $langs = array_merge($langs, $langs_custom);
        if (!is_array($attr)) {
            return $langs[$attr];
        } else {
            return vsprintf($langs[$attr[0]], $attr[1]);
        }
    }
}