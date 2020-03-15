<?php

namespace king\lib;

use king\core\Instance;
use king\lib\Session;
use king\lib\Cache;

class Captcha extends Instance
{
    private $caps;
    private $image;
    private $word_number = 3;
    private $font;
    protected $use_cache = false;
    protected $code = false;

    public function __construct()
    {
        $this->caps = C('captcha.*');
        $this->path = SYS_PATH . 'lib/captcha/';
        if (!isset($this->caps['store']) || $this->caps['store'] == 'session') {
            Session::getClass();
        } else {
            $this->use_cache = true;
        }
    }

    public function setWordNumber($number)
    {
        $this->word_number = $number;
    }

    private function getResponse()
    {
        $this->charset = isset($this->caps['charset']) ? $this->caps['charset'] : 'utf-8';
        if ($this->caps['lang'] == 'en') {
            $this->font = $this->path . 'en.ttf';
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            $this->font = $this->path . 'cn.ttf';
            $pool = require($this->path . 'dict.php');
        }
        return $this->getText($pool, $this->word_number, $this->caps['lang']);
    }

    public function render($header = true)
    {
        $this->image = imagecreatetruecolor($this->caps['width'], $this->caps['height']);

        $color1 = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $color2 = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $this->gradient($color1, $color2);//随机颜色

        $response = $this->getResponse();
        $x = isset($this->caps['init_pos']) ? rand($this->caps['init_pos'][0], $this->caps['init_pos'][1]) : rand(5, 15);
        $colorR = mt_rand(150, 255);
        $colorG = mt_rand(200, 255);
        $colorB = mt_rand(200, 255);
        $fontColor = imagecolorallocate($this->image, $colorR, $colorG, $colorB);
        for ($i = 0, $strlen = mb_strlen($response, $this->charset); $i < $strlen; $i++) {
            $angle = mt_rand(-40, 20);
            $size = isset($this->caps['font_size']) ? rand($this->caps['font_size'][0], $this->caps['font_size'][1]) : rand(12, 14);
            $font = $this->getFont();
            $char = mb_substr($response, $i, 1, $this->charset);
            $box = imageftbbox($size, $angle, $font, $char);
            $y = $this->caps['height'] / 2 + ($box[2] - $box[5]) / 4;
            imagefttext($this->image, $size, $angle, $x, $y, $fontColor, $font, $char);
            $x += $box[2] + 10;
        }

        if ($this->caps['line'] == true) {
            $this->line($colorR, $colorG, $colorB);
        }

        if ($this->caps['warping'] == true) {
            $this->setWarping();
        }

        if ($this->caps['circle'] == true) {
            $this->circle();
        }

        if ($this->use_cache) {
            $this->code = uniqid();
            Cache::set('code:' . $this->code, sha1(strtolower($response)), 90);
        } else {
            Session::set('code', sha1(strtolower($response)));
        }

        return $this->img($header, $this->code);
    }

    private function getFont()
    {
        if (isset($this->caps['fonts'])) {
            $key = array_rand($this->caps['fonts']);
            return $this->path . $this->caps['fonts'][$key] . '.ttf';
        } else {
            return $this->font;
        }
    }

    private function getFontSize()
    {
        if (isset($this->caps['font_size'])) {
            $key = array_rand($this->caps['font_size']);
            return $this->path . $this->caps['font_size'][$key] . '.ttf';
        } else {
            return $this->font;
        }
    }

    public function valid($captcha = '', $session_id = '')
    {
        if ($this->use_cache) {
            $code = Cache::get('code:' . $session_id);
        } else {
            if ($session_id) {
                session_id($session_id);
            }
            $code = Session::get('code');
        }

        if (sha1(strtolower($captcha)) == $code) {
            Cache::del('code:' . $session_id);
            return true;
        } else {
            return false;
        }
    }

    private function getText($pool, $len, $encoding = 'en')
    {
        $max = mb_strlen($pool, $this->charset) - 1;
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $num = mt_rand(0, $max);
            if ($encoding == 'cn') {
                $str .= mb_substr($pool, $num, 1, $this->charset);
            } else {
                $str .= $pool{$num};
            }
        }
        return $str;
    }

    public function img($header, $code)
    {
        if ($header) {
            header('Content-Type: image/png');
            imagepng($this->image);
            imagedestroy($this->image);
        } else {
            ob_start();
            imagepng($this->image);
            $output = ob_get_contents();
            ob_end_clean();
            return json_encode(['img' => 'data:image/png;base64,' . base64_encode($output), 'code' => $code]);
        }
    }

    public function gradient($color1, $color2)//随机颜色
    {
        $color1 = imagecolorsforindex($this->image, $color1);
        $color2 = imagecolorsforindex($this->image, $color2);
        $steps = $this->caps['width'];

        $r1 = ($color1['red'] - $color2['red']) / $steps;
        $g1 = ($color1['green'] - $color2['green']) / $steps;
        $b1 = ($color1['blue'] - $color2['blue']) / $steps;

        $x1 = &$i;
        $y1 = 0;
        $x2 = &$i;
        $y2 = $this->caps['height'];

        for ($i = 0; $i <= $steps; $i++) {
            $r2 = $color1['red'] - floor($i * $r1);
            $g2 = $color1['green'] - floor($i * $g1);
            $b2 = $color1['blue'] - floor($i * $b1);
            $color = imagecolorallocate($this->image, $r2, $g2, $b2);
            imageline($this->image, $x1, $y1, $x2, $y2, $color);
        }
    }

    public function line($colorR, $colorG, $colorB)
    {
        $A = mt_rand(4, 8);  // 振幅
        $f = mt_rand(3, 5);  // X轴方向偏移量
        $T = 40;  // 周期
        $w = 0.05;

        $px1 = isset($this->caps['init_pos']) ? rand($this->caps['init_pos'][0], $this->caps['init_pos'][1]) : rand(5, 15);
        $px2 = round($this->caps['width'] - $px1);
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $this->caps['height'] / 2;  // y = Asin(ωx+φ)
                $i = (int)((15 - 6) / 4);
                while ($i > 0) {
                    imagesetpixel($this->image, $px + $i, $py + $i, imagecolorallocate($this->image, $colorR, $colorG, $colorB));
                    $i--;
                }
            }
        }
    }

    public function circle()
    {
        for ($i = 0, $count = mt_rand(10, 14); $i < $count; $i++)//随机圆圈
        {
            $color = imagecolorallocatealpha($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(50, 120));
            $size = mt_rand(7, $this->caps['height'] / 3);
            imagefilledellipse($this->image, mt_rand(0, $this->caps['width']), mt_rand(0, $this->caps['height']), $size, $size, $color);
        }
    }

    public function setWarping()
    {
        $rgb = [];
        $direct = rand(-4, -2);
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $level = $width / 40;
        for ($j = 0; $j < $height; $j++) {
            for ($i = 0; $i < $width; $i++) {
                $rgb[$i] = imagecolorat($this->image, $i, $j);
            }

            for ($i = 20; $i < $width; $i++) {
                $r = sin($j / $height * 2 * M_PI - M_PI * 0.7) * $direct;
                imagesetpixel($this->image, $i + $r, $j, $rgb[$i]);
            }
        }
    }
}