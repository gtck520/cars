<?php

use king\core\Error;
use king\core\Loader;
use king\core\Route;
use king\lib\Input;
use king\View;
use king\lib\Request;
use king\lib\Response;
use king\core\Db;

function C($name)//加载config目录下的文件
{
    $name = strtolower($name);
    if (!strpos($name, '.')) {
        $file = 'config';
        $key = $name;
    } else {
        $files = explode('.', $name);
        $file = $files[0];
        $key = $files[1];
    }

    if (!isset(Loader::$cache_file['config'][$file])) {
        $file = APP_PATH . 'config' . DS . $file . EXT;
        if (is_file($file)) {
            $array = Loader::$cache_file['config'][$file] = require $file;
            if ($key == '*') {
                return $array;
            } else {
                return $array[$key] ?? '';
            }
        } else {
            return false;
        }
    }
}

function view($mix = '', $data = [])
{
    return View::getClass($mix, $data);
}

function P($key = '', $default = null, $xss = true)
{
    return Input::post($key, $default, $xss);
}

function G($key = '', $default = null, $xss = true)
{
    return Input::get($key, $default, $xss);
}

function H($name = '')
{
    return Request::header($name);
}

function Put()
{
    return Response::put();
}

function S($seg)  // Input::segment的简写
{
    return Input::segment($seg);
}

function A()  // Input::getArgs的简写
{
    return Input::getArgs();
}

function redirect($url = '')
{
    return Input::redirect($url);
}

function L($url = '')  // $this->input->site的简写
{
    return Input::site($url);
}

function M($table = '', $db = 'default') // 仅适合脚本场景
{
    static $dbs;
    if (!isset($dbs[$db])) {
        $dbs[$db] = new Db($db);
    }

    if ($table) {
        $dbs[$db]->setTable($table);
    }
    return $dbs[$db];
}

function https()
{
    if ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || !empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        return true;
    } else {
        return false;
    }
}

function U($url = '', $prefix = '') //prefix参数css,js会用到,一般就是component,可以直接调用UC方法
{
    $parts = pathinfo($url);
    if (isset($parts['extension']) && $parts['extension'] != C('suffix')) {
        $ext_str = substr($parts['extension'], 0, 2);
        $url_prefix = input::libUrl();
        if ($ext_str == 'cs') {
            $ext = 'css/';
        } elseif ($ext_str == 'js') {
            $ext = 'js/';
        } else {
            $ext = 'img/';
        }
    } else {
        $url_prefix = Input::site();
        $ext = '';
    }

    if (!empty($prefix)) {
        $return_url = $prefix . '/' . $url;
    } else {
        $segs = Route::sourceSeg();
        if ($ext && (in_array($segs[0], Route::getRootFolder()))) {
            $return_url = $segs[0] . '/' . $ext . $url;
        } elseif (in_array(S(1), Route::getRootFolder())) {
            $return_url = S(1) . '/' . $ext . $url;
        } else {
            $return_url = $ext . $url;
        }
    }

    if (!$ext) {
        $return_url = Route::reversePregUrl($return_url);
    }
    return $url_prefix . $return_url;
}

function UC($url = '')
{
    return U($url, 'component');
}

function dd(...$data)
{
    foreach ($data as $value) {
        print_r($value);
        echo PHP_EOL;
    }
    die();
}