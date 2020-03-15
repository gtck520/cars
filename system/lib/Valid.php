<?php

namespace king\lib;

use king\lib\Lang;
use king\lib\exception\BadRequestHttpException;

class Valid
{
    private static $instance;
    private $error = false;
    private $rules = [];
    private $class;
    private $scene;
    private $current;
    public $data;

    public static function getClass($data = '', $current = '')
    {
        return new Valid($data, $current);
    }

    public function __construct($data = '', $current)
    {
        $this->data = $data ?: P();
        $this->current = $current;
    }

    public function setScene($scene)
    {
        $this->scene = $scene;
    }

    public function addRule($field, $rules, $label = '')
    {
        if ($this->current) {
            if (!is_array($this->scene)) {
                throw new BadRequestHttpException('调用场景时, 必须设置验证场景规则');
            }
            if (!isset($this->scene[$this->current])) {
                throw new BadRequestHttpException($this->current . '验证场景不存在');
            }
            $keys = $this->scene[$this->current] ?? '';
            if (in_array($field, $keys)) {
                $this->rules[] = [$field, $rules, $label];
            }
        } else {
            $this->rules[] = [$field, $rules, $label];
        }
    }

    public function run($class = '')//运行规则
    {
        foreach ($this->rules as $rule) {
            $field = $rule[0];
            $multiRule = $rule[1];
            $label = $rule[2] ?: $field;
            if (isset($this->data[$field]) && (strpos($multiRule, 'required') !== false || $this->data[$field] !== '')) {
                $rules = explode('|', $multiRule);
                foreach ($rules as $oneRule) {
                    $realRule = $oneRule;
                    $param = '';
                    if (strpos($oneRule, ',') !== false) {
                        $tmpRule = explode(',', $oneRule);
                        $realRule = $tmpRule[0];
                        if ($realRule == 'in') {
                            array_shift($tmpRule);
                            $param = $tmpRule;
                            if (strpos($param[0], '[') !== false) {
                                $param_str = str_replace(['[', ']'], '', implode(',', $param));
                                $param = explode(',', $param_str);
                            }
                        } else {
                            $param = $tmpRule[1];
                        }
                    }

                    if (method_exists($this, $realRule)) {
                        if ($param !== '') {
                            $this->$realRule($this->data[$field], $param, $label);
                        } else {
                            $this->$realRule($this->data[$field], $label);
                        }
                    } else {
                        $class = $class ?: \king\core\Loader::$run_class;
                        if (method_exists($class, $realRule)) {
                            $class = new $class;
                            $class->$realRule($this->data[$field], $this);
                        } else {
                            $this->setError(Lang::get(['valid rule not found', [$realRule]]));
                        }
                    }

                    if ($this->getError() != '') { // 如果有问题直接返回
                        return false;
                    }
                }
            } else {
                if (!isset($this->data[$field])) {
                    $label = $label ?: $field;
                    if (strpos($multiRule, 'required') !== false) {
                        return $this->setError(Lang::get(['valid field not set', [$label]])); // 字段问题直接返回
                    }
                }
            }
        }

        return true;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($newError)
    {
        $this->error = $newError;
        return false;
    }

    public function response($class = '')
    {
        if (!$this->run($class)) {
            throw new BadRequestHttpException($this->getError());
        }
    }

    public function required($value, $label = '')
    {
        if (!is_array($value)) {
            if (!isset($value)) {
                $this->setError(Lang::get(['valid require', [$label]]));
            }
        }
    }

    public function minLength($value, $val, $label = '')
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($value) < $val) ? $this->setError(Lang::get(['valid minLength', [$label, $val]])) : true;
        }

        return (strlen($value) < $val) ? $this->setError(Lang::get(['valid minLength', [$label, $val]])) : true;
    }

    public function maxLength($value, $val, $label = '')
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($value) > $val) ? $this->setError(Lang::get(['valid maxLength', [$label, $val]])) : true;
        }

        return (strlen($value) > $val) ? $this->setError(Lang::get(['valid maxLength', [$label, $val]])) : true;
    }

    public function email($value, $label = '')
    {
        $label = $label ? $label : '邮箱';
        if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $value)) {
            $this->setError(Lang::get('valid email'));
        }
    }

    public function mobile($value, $label = '')
    {
        $label = $label ? $label : '手机号';
        if (!preg_match("/^(13|14|15|16|17|18|19)[0-9]{9}$/", $value)) {
            $this->setError(Lang::get('valid mobile'));
        }
    }

    public function url($value, $label = '')
    {
        $label = $label ? $label : '网址';
        $return = filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
        if (!$return) {
            $this->setError(Lang::get('valid url'));
        }
    }

    public function ip($value, $label = '')
    {
        $label = $label ? $label : 'ip地址';
        $flags = FILTER_FLAG_NO_RES_RANGE;
        $return = filter_var($value, FILTER_VALIDATE_IP, $flags);
        if (!$return) {
            $this->setError(Lang::get('valid ip'));
        }
    }

    public function alpha($value, $label) // 字母
    {
        if (!preg_match("/^([a-z])+$/i", $value)) {
            $this->setError(Lang::get(['valid alpha', [$label]]));
        } else
            return true;
    }

    public function alphaDash($value, $label) // 数字字母下划线
    {
        if (!preg_match("/^([-a-z0-9_-])+$/i", $value)) {
            $this->setError(Lang::get(['valid alphaDash', [$label]]));
        }
    }

    public function isInt($value, $label)
    {
        if (!preg_match('/^[-+]?[0-9]+$/', $value)) {
            $this->setError(Lang::get(['valid isInt', [$label]]));
        }
    }

    public function int($value, $label)
    {
        return $this->isInt($value, $label);
    }

    public function gt($value, $min, $label)
    {
        if (!is_numeric($value)) {
            $this->setError(Lang::get(['valid numeric', [$label]]));
        } elseif ($value <= $min) {
            $this->setError(Lang::get(['valid gt', [$label, $min]]));
        }
    }

    public function gte($value, $min, $label)
    {
        if (!is_numeric($value)) {
            $this->setError(Lang::get(['valid numeric', [$label]]));
        } elseif ($value < $min) {
            $this->setError(Lang::get(['valid gte', [$label, $min]]));
        }
    }

    public function lt($value, $max, $label)
    {
        if (!is_numeric($value)) {
            $this->setError(Lang::get(['valid numeric', [$label]]));
        } elseif ($value >= $max) {
            $this->setError(Lang::get(['valid lt', [$label, $max]]));
        }
    }

    public function lte($value, $max, $label)
    {
        if (!is_numeric($value)) {
            $this->setError(Lang::get(['valid numeric', [$label]]));
        } elseif ($value > $max) {
            $this->setError(Lang::get(['valid lte', [$label, $max]]));
        }
    }

    public function size($value, $size, $label)
    {
        $value = trim($value);
        if (is_array($value)) {
            $length = count($value);
        } else {
            $length = strlen((string)$value);
        }
        if ($length != $size) {
            $this->setError(Lang::get(['valid size', [$label, $size]]));
        }
    }

    public function confirm($value, $val, $label)
    {
        if (!isset($this->data[$val])) {
            $this->setError(Lang::get(['valid data not found', [$val]]));
        } else {
            $val = $this->data[$val];
            if ($value <> $val) {
                $this->setError(Lang::get(['valid confirm', [$label]]));
            }
        }
    }

    public function equal($value, $val, $label)
    {
        if (preg_match('/[^0-9\.]/', $val)) {
            $this->setError(Lang::get(['valid numeric', [$label]]));
        } elseif ($value <> $val) {
            $this->setError(Lang::get(['valid equal', [$label, $val]]));
        }
    }

    public function isArray($value, $label)
    {
        if (!is_array($value)) {
            $this->setError(Lang::get(['valid isArray', [$label]]));
        }
    }

    public function array($value, $label)
    {
        return $this->isArray($value, $label);
    }

    public function in($value = [], $val, $label)
    {
        if (!in_array($value, $val)) {
            $this->setError(Lang::get(['valid in', [$label]]));
        }
    }

    public function dateTime($val, $label)
    {
        if (!preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $val)) {
            $this->setError(Lang::get(['valid dateTime', [$label]]));
        }
    }

    public function isDate($val, $label)
    {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $val)) {
            $this->setError(Lang::get(['valid date', [$label]]));
        }
    }

    public function date($val, $label)
    {
        $this->isDate($val, $label);
    }

    public function isFloat($val, $label)
    {
        if (!(preg_match('/^[-+]?[0-9]*\.?[0-9]+$/', $val))) {
            $this->setError(Lang::get(['valid isFloat', [$label]]));
        }
    }

    public function float($val, $label)
    {
        return $this->isFloat($val, $label);
    }
}
