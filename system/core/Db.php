<?php

namespace king\core;

use PDO;
use king\core\Error;
use king\lib\Pagination;
use king\lib\Log;

class Db
{
    protected $links = [];
    protected $db;
    private $dbs;
    protected $table;
    public static $debug = false;
    private $special_opt = false;
    private $current_link;
    private $db_set = '';
    private $max_slave = 0;
    private $select = [];
    private $slave_name = '';
    private $transaction = false;
    protected $timestamp = false;
    protected $key = 'id';
    protected $fetch_type = PDO::FETCH_ASSOC;
    protected $stmt;
    protected $bind = [];
    protected $wheres;
    protected $bind_where = [];
    protected $bind_update = [];

    public function __construct($db_set)
    {
        $this->dbs = C('database.*');
        $this->db_set = $db_set;
        if (isset($this->dbs[$db_set])) {
            $this->db = $this->dbs[$db_set];
        } else {
            Error::showError('db instance:' . $db_set . ' not found');
        }

        $this->slave_name = $db_set . '_slave';
        if (isset($this->dbs[$this->slave_name])) {
            $this->max_slave = count($this->dbs[$this->slave_name]);
        }
    }

    public function setTable($table)
    {
        $this->table = $this->db['prefix'] . $table;
        $this->initSelect();
        return $this;
    }

    public function startMaster() // 强制切换主库,使用事务来模拟实际不启动事务
    {
        $this->transaction = true;
        $this->current_link = $this->links[$this->db_set . '_master'] ?? '';
        is_object($this->current_link) or $this->connect(false);
    }

    public function endMaster() // 结束强制切换主库
    {
        $this->transaction = false;
    }

    public function allowSpecialOpt()
    {
        $this->special_opt = true;
    }

    public function setDebug($record = 'echo')
    {
        static::$debug = $record;
    }

    private function connect($select = '')
    {
        $options = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if ($this->max_slave > 0 && $select == true && $this->transaction == false) {
            $rand = mt_rand(1, $this->max_slave) - 1;
            $this->db = $this->dbs[$this->slave_name][$rand];
            $link_id = $this->db_set . '_slave_' . $rand;
        } else {
            $link_id = $this->db_set . '_master';
            $this->db = $this->dbs[$this->db_set];
        }

        if (!isset($this->links[$link_id])) {
            $this->timestamp = $this->db['timestamp'] ?? false;
            $port = $this->db['port'] ?? '3306';
            $charset = $this->db['charset'] ?? 'utf8mb4';
            $dsn = 'mysql:host=' . $this->db['host'] . ';port=' . $port . ';dbname=' . $this->db['db'] . ';charset=' . $charset;

            try {
                $this->links[$link_id] = new PDO($dsn, $this->db['user'], $this->db['password'], $options);
            } catch (\PDOException $e) {
                Log::write($e->getMessage());
                Error::showError('连接失败,请查看系统日志');
            }
        }

        $this->current_link = $this->links[$link_id];
    }

//    protected function bindValue(array $bind = [])
//    {
//        foreach ($bind as $key => $val) {
//            if (is_array($val)) {
//                $result = $this->stmt->bindValue($key, $val[0], $val[1]);
//            } else {
//                $result = $this->stmt->bindValue($key, $this->escapeValue($val));
//            }
//
//            if (!$result) {
//                Error::showError('参数绑定失败' . $key);
//            }
//        }
//    }

//    protected function getFieldBindType($value)
//    {
//        if (is_bool($value) === true) {
//            return PDO::PARAM_BOOL;
//        } elseif (is_int($value) === true) {
//            return PDO::PARAM_INT;
//        } elseif (is_null($value) === true) {
//            return PDO::PARAM_NULL;
//        } else {
//            return PDO::PARAM_STR;
//        }
//    }

    private function getBind($item)
    {
        return isset($this->bind[$item]);
    }

    private function quote($value)
    {
        is_object($this->current_link) or $this->connect();

        return $this->current_link->quote($value);
    }

    protected function escapeValue($value)
    {
        is_object($this->current_link) or $this->connect();
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        } else {
            $value = $this->quote($value);
        }

        return $value;
    }

    public function e($value)
    {
        return $this->escapeValue($value);
    }

    public function field($fields = '*')
    {
        $fields = $fields ? $this->escapeFields($fields) : '*';
        $this->select['select'] = 'SELECT ' . $fields;
        return $this;
    }

    protected function escapeFields($fields)
    {
        if ($fields == '*') {
            return $fields;
        }

        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        $field_array = array_map([$this, 'escapeColumn'], $fields);

        return implode(',', $field_array);
    }

    protected function initSelect()
    {
        $this->bind = [];
        $this->bind_update = [];
        $this->wheres = '';
        $this->bind_where = [];
        $this->select = [
            'select' => 'SELECT *',
            'from' => 'FROM ' . $this->getTable()
        ];
    }

    public function from($table)
    {
        $this->select['from'] = 'FROM ' . $this->getTable($table);
        return $this;
    }

    protected function checkRaw($value)
    {
        $values = explode(',', $value);
        if (end($values) == 'raw') {
            return str_replace(',raw', '', $value);
        } else {
            return '?';
        }
    }

    protected function setBind($value)
    {
        if ($this->checkRaw($value) == '?') {
            $this->bind[] = $value;
        }
    }

    public function where($wh = '', $mode = '', $value = '')
    {
        if ($wh instanceof \Closure || $mode !== '') {
            if ($value === '') {
                $value = $mode;
                $mode = '=';
            }
            return $this->whereNew($wh, $mode, $value);
        } else {
            if (is_array($wh) && count($wh) > 0) {
                $whs = [];
                foreach ($wh as $key => $value) {
                    $field_key = $this->checkRaw($key);
                    $match = $this->hasOpera($key);
                    if ($field_key != '?') {
                        if (!empty($match[0])) {
                            $whs[] = $this->escapeColumn($field_key, 'raw') . $this->checkRaw($value);
                        } else {
                            $whs[] = $this->escapeColumn($field_key, 'raw') . '=' . $this->checkRaw($value);
                        }

                        $this->setBind($value);
                    } else {
                        $field_key = $key;
                        if (!empty($match[0])) {
                            $source_key = trim(str_replace($match[0], '', $field_key));
                            $pdo_key = $this->escapeColumn($source_key) . ' ' . strtoupper($match[0]) . ' ';
                            $opera = strtolower(trim($match[0]));

                            if ($opera == 'between' || $opera == 'not between') {
                                $between_key = '? AND ?';
                                $whs[] = $pdo_key . ' ' . $between_key;
                                $this->bind[] = $value[0];
                                $this->bind[] = $value[1];
                            } elseif ($opera == 'in' || $opera == 'not in') {
                                if (is_array($value)) {
                                    $place_holders = '(' . implode(',', array_fill(0, count($value), '?')) . ')';
                                    $whs[] = $pdo_key . $place_holders;
                                    $this->bind = array_merge($this->bind, array_values($value));
                                } else {
                                    $whs[] = $pdo_key . $this->checkRaw($value);
                                }
                            } else {
                                $whs[] = $pdo_key . $this->checkRaw($value);
                                $this->setBind($value);
                            }
                        } else {
                            $pdo_key = $this->escapeColumn($field_key);
                            $whs[] = $pdo_key . ' = ' . $this->checkRaw($value);
                            $this->setBind($value);
                        }
                    }
                }

                if ($mode == '') {
                    $this->wheres .= 'WHERE ' . implode(' AND ', $whs);
                }
            } else {
                $this->wheres .= $wh ? 'WHERE ' . $wh : '';
            }

            $first_mode = strtolower(trim(substr(trim($this->wheres), 0, 3)));
            if ($first_mode == 'and' || $first_mode == 'or') {
                $this->wheres = substr_replace(trim($this->wheres), ' WHERE 1 ' . strtoupper($first_mode), 0, 3);
            }
            $this->select['where'] = $this->wheres;
            return $this;
        }
    }

    public function whereNew($key = '', $opera = '=', $value = '', $union = '')
    {
        $opera = (!is_null($value)) ? $opera : '';
        if ($key instanceof \Closure) {
            $this->bind_where[] = (count($this->bind_where) > 0) ? $union . '(' : '(';
            call_user_func($key, $this);
            $this->bind_where[] = ')';
        } else {
            $field = $this->escapeColumn($key);
            $pre_value = '';
            if ($opera == 'between') {
                $pre_value = '? AND ?';
                if (!is_array($value)) {
                    Error::showError('between 值必须为数组');
                } else {
                    $this->bind[] = $value[0];
                    $this->bind[] = $value[1];
                }
            } elseif ($opera == 'in' || $opera == 'not in') {
                if (is_array($value)) {
                    $pre_value = '(' . implode(',', array_fill(0, count($value), '?')) . ')';
                    $this->bind = array_merge($this->bind, array_values($value));
                } else {
                    $pre_value = '(' . $value . ')';
                }
            } else {
                if (!is_null($value)) {
                    $pre_value = $this->checkRaw($value);
                    $this->bind[] = $value;
                }
            }

            $pre_where = $field . ' ' . $opera . ' ' . $pre_value;
            if ($union == 'inner') {
                $where = 'OR ' . $pre_where;
                $union = false;
            } else {
                $where = (count($this->bind_where) > 0) ? ($union ?: ' AND ') . $pre_where : $pre_where;
            }

            if (!empty($union)) {
                if (count($this->bind_where) > 0) {
                    $this->bind_where[] = str_replace($union, $union . ' (', $where) . ')';
                } else {
                    $this->bind_where[] = $pre_where;
                }
            } else {
                $this->bind_where[] = $where;
            }
        }

        return $this;
    }

    public function andWhere($key = '', $opera = '=', $value = '')
    {
        return $this->whereNew($key, $opera, $value, 'AND');
    }

    public function orWhere($key = '', $opera = '=', $value = '')
    {
        if ($key instanceof \Closure) {
            return $this->whereNew($key, $opera, $value, 'OR');
        } else {
            if ($value === '') {
                $value = $opera;
                $opera = '=';
            }

            return $this->whereNew($key, $opera, $value, 'inner');
        }
    }

    /*
     *
     * whereOr和orWhere合法已合并,该方法已废弃
     */
    public function whereOr($key = '', $opera = '=', $value = '')
    {
        if ($value === '') {
            $value = $opera;
            $opera = '=';
        }

        return $this->whereNew($key, $opera, $value, 'inner');
    }

    public function limit($start = 0, $limit = 10)
    {
        if (is_array($start)) {
            $limit = $start[1] ?? 0;
            $start = $start[0];
        }

        $this->select['limit'] = 'LIMIT ' . intval($start) . ($limit ? ',' . intval($limit) : '');

        return $this;
    }

    public function find($id = '')
    {
        if ($id) {
            $this->where([$this->key => $id]);
        }
        $this->limit(0, 1);
        return $this->get(['find', $id]);
    }

    public function get($param = '')
    {
        $sql = $this->buildSql();
        return $this->query($sql, $param);
    }

    public function value($field = '')
    {
        if ($field) {
            $this->field($field);
        } else {
            $field = str_replace('`', '', substr($this->select['select'], 7));
            if (!$field) {
                Error::showError('value方法必须定义字段');
            }
        }

        $this->limit(0, 1);

        return $this->get(['value', $field]);
    }

    public function column($column = '')
    {
        if ($column) {
            $this->field($column);
        } else {
            $column = $this->getSelectColumn();
            if (!$column) {
                Error::showError('value方法必须定义字段');
            }
        }

        return $this->get(['column', $column]);
    }

    protected function getSelectColumn()
    {
        return str_replace('`', '', substr($this->select['select'], 7));
    }

    protected function buildSql($select = '')
    {
        $sql = '';
        $params = $select ?: $this->select;
        if (count($this->bind_where) > 0) {
            $params['where'] = $this->getBindWhere();
            $params = $this->sortSql($params);
        }

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $sql .= $v . ' ';
                }
            } else {
                $sql .= $value . ' ';
            }
        }

        return trim($sql);
    }

    private function getBindWhere()
    {
        if (count($this->bind_where) > 0) {
            $where = 'WHERE ' . implode(' ', $this->bind_where);
            return str_replace('(  AND', '(', $where);
        }
    }

    public function query($sql, $param = '')
    {
        $select = false;
        $match = preg_match('#\b(?:INSERT|UPDATE|REPLACE|DELETE|SELECT)\b#i', $sql); // 只允许执行select,insert,update,delete,replace五种方法
        if (strtolower(substr(trim($sql), 0, 6)) == 'select') {
            $select = true;
        }

        $this->connect($select);
        if ($this->special_opt || $match) {
            if (self::$debug == 'echo') {
                echo 'prepare:' . substr($sql, 0, 500) . '<br />';
            } elseif (self::$debug == 'log') {
                Log::write('prepare:' . $sql);
            }
            $this->stmt = $this->current_link->prepare($sql);
            $return = false;
            try {
                $rs = $this->stmt->execute($this->bind);

                if (self::$debug == 'echo') {
                    echo $this->getLastQuery($sql, $this->bind) . '<br />';
                } elseif (self::$debug == 'log') {
                    Log::write($this->getLastQuery($sql, $this->bind));
                }

                if ($rs) {
                    if (is_array($param)) {
                        switch ($param[0]) {
                            case 'insert':
                                $return = ($this->current_link->lastInsertId() > 0) ? $this->current_link->lastInsertId() : true;
                                break;
                            case 'update':
                                $return = $this->stmt->rowCount();
                                break;
                            case 'find':
                                $return = $this->stmt->fetch(PDO::FETCH_ASSOC);
                                break;
                            case 'column':
                                $rs_all = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                                $return = array_column($rs_all, $param[1]);
                                break;
                            case 'chunk':
                                $return = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                                break;
                            case 'value':
                                $return = $this->stmt->fetch(PDO::FETCH_COLUMN);
                                break;
                            case 'page':
                                $return = $this->stmt->fetch(PDO::FETCH_COLUMN);
                                break;
                            case 'delete':
                                $return = $rs;
                                break;
                            default:
                                break;
                        }
                    } else {
                        $return = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }
            } catch (\PDOException $e) {
                Log::write($e->getMessage());
                Error::showError('数据库操作失败,请查看系统日志');
            }

            if (empty($param[0]) || ($param[0] != 'page' && $param[0] != 'chunk')) {
                $this->initSelect();
            }

            return $return;
        } else {
            return false;
        }
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    protected function getLastQuery($sql, $bind)
    {
        if (is_array($sql)) {
            $sql = implode(';', $sql);
        }

        if (is_array($bind)) {
            foreach ($bind as $value) {
                $value = ($value === '') ? "''" : $value;
                $sql = substr_replace($sql, $value, strpos($sql, '?'), 1);
            }
        }

        return rtrim(substr($sql, 0, 500));
    }

    private function checkDateTime($date)
    {
        if (preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $date)) {
            return true;
        }
    }

    private function checkTimestamp($key)
    {
        $keys = explode('_', $key);
        if ('_' . end($keys) == $this->timestamp) {
            return true;
        }
    }

    private function hasOpera($str)
    {
        preg_match('/(>\=|<\=|!\=|<>)|[<>!=]|\s(IS(\s+NOT){0,1}|BETWEEN|LIKE|IN|OR|NOT\s(IN|LIKE|BETWEEN))/i', trim($str), $match);

        return $match;
    }

    public function update($upd)
    {
        foreach ($upd as $key => $val) {
            $len = strlen($key) - 1;
            $fields = unpack('A' . $len . 'real_key/A1opera', $key);
            if ($fields['opera'] == '+' || $fields['opera'] == '-') {
                $valstr[] = $this->escapeColumn($fields['real_key']) . '=' . $this->escapeColumn($fields['real_key']) . $fields['opera'] . floatval($val);
            } else {
                $valstr[] = $this->escapeColumn($key) . '= ?';
                $this->bind_update[] = $val;
            }
        }
        $this->bind = array_merge($this->bind_update, $this->bind);
        $where = $this->select['where'] ?? $this->getBindWhere();
        if (!$where) {
            Error::showError('update必须设置where条件');
        }

        return $this->query('UPDATE ' . $this->getTable() . ' SET ' . implode(', ', $valstr) . ' ' . $where, ['update']);
    }

    private function bindParams($params)
    {
        $rs = [];
        foreach ($params as $key => $value) {
            $rs['keys'][] = $this->escapeColumn($key);
            $rs['bind_keys'][] = '?';
            $this->bind[] = $value;
        }

        return $rs;
    }

    public function save($params)
    {
        if (isset($params[$this->key])) {
            $this->where([$this->key => $params[$this->key]]);
            unset($params[$this->key]);
            return $this->update($params);
        } else {
            return $this->insert($params);
        }
    }

    public function replace($params)
    {
        $array = $this->bindParams($params);
        return $this->query('REPLACE INTO ' . $this->getTable() . ' (' . implode(', ', $array['keys']) . ') VALUES (' . implode(',', $array['bind_keys']) . ')', ['update']);
    }

    public function insert($params)
    {
        $array = $this->bindParams($params);
        return $this->query('INSERT INTO ' . $this->getTable() . ' (' . implode(', ', $array['keys']) . ') VALUES (' . implode(',', $array['bind_keys']) . ')', ['insert']);
    }

    protected function placeholders($text, $count = 0, $separator = ",")
    {
        $result = array();
        if ($count > 0) {
            for ($x = 0; $x < $count; $x++) {
                $result[] = $text;
            }
        }

        return implode($separator, $result);
    }

    public function batchInsert($params)
    {
        $keys = array_keys(current($params));
        foreach ($keys as $key => $value) {
            $keys[$key] = $this->escapeColumn($value);
        }

        $insert_values = array();
        foreach ($params as $d) {
            $question_marks[] = '(' . $this->placeholders('?', sizeof($d)) . ')';
            $insert_values = array_merge($insert_values, array_values($d));
        }

        $this->bind = $insert_values;
        return $this->query('INSERT INTO ' . $this->getTable() . ' (' . implode(', ', $keys) . ') VALUES ' . implode(',', $question_marks), ['insert']);
    }

    public function getTable($table = '')
    {
        return $table ? $this->escapeTable($this->db['prefix'] . $table) : $this->escapeTable($this->table);
    }

    private function escapeTable($table)
    {
        $tables = explode(' ', $table);
        if (isset($tables[1])) {
            return '`' . str_replace('.', '`.`', $tables[0]) . '`' . ' ' . $tables[1];
        } else {
            return '`' . str_replace('.', '`.`', $tables[0]) . '`';
        }
    }

    protected function escapeColumn($column, $raw = '')
    {
        $columns = explode(',', $column);
        if ($columns[0] == 'raw') {
            Error::showError('带raw的字段只能传数组');
        }

        if (end($columns) == 'raw') {
            $column = str_replace(',raw', '', $column);
            return $this->escapeColumn($column, true);
        }

        if ($column == '*' || $raw) {
            return $column;
        } else {
            $column = trim($columns[0]);
        }

        if (preg_match('/(avg|count|sum|max|min)\(\s*(.*)\s*\)(\s*as\s*(.+)?)?/i', $column, $matches)) {
            if (count($matches) == 3) {
                return $matches[1] . '(' . $this->escapeColumn($matches[2]) . ')';
            } else if (count($matches) == 5) {
                return $matches[1] . '(' . $this->escapeColumn($matches[2]) . ') AS ' . $this->escapeColumn($matches[4]);
            }
        }

        if (!preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column)) {
            if (stripos($column, ' AS ') !== false) {
                $column = str_ireplace(' AS ', ' AS ', $column);
                $column = array_map([$this, __FUNCTION__], explode(' AS ', $column));
                return implode(' AS ', $column);
            }

            return preg_replace('/[^.*]+/', '`$0`', $column);
        }
        $parts = explode(' ', $column);
        $column = '';

        for ($i = 0, $c = count($parts); $i < $c; $i++) {
            if ($i == ($c - 1)) {
                $column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
            } else {
                $column .= $parts[$i] . ' ';
            }
        }
        return $column;
    }

    public function startTrans()
    {
        $this->transaction = true;
        $this->current_link = $this->links[$this->db_set . '_master'] ?? '';
        is_object($this->current_link) or $this->connect(false);
        $this->current_link->beginTransaction();
    }

    public function endTrans()
    {
        $this->current_link->commit();
        $this->transaction = false;
    }

    public function rollback()
    {
        $this->current_link->rollBack();
    }

    public function delete($wh = ['id' => 0])
    {
        if (count($this->bind_where) > 0) {
            $where = $this->getBindWhere();
        } else {
            if (is_array($wh) && count($wh) > 0) {
                $this->where($wh);
                $where = $this->select['where'];
            }
        }

        if ($where) {
            return $this->query('DELETE FROM ' . $this->getTable() . ' ' . $where, ['delete']);
        }
    }

    protected function convertOrder($order)
    {
        $prefix = 'ORDER BY';
        $orders = [];
        $types = ['ASC', 'DESC'];
        if (is_array($order)) {
            foreach ($order as $key => $value) {
                $value = strtoupper($value);
                if (in_array($value, $types)) {
                    $orders[] = $this->escapeColumn($key) . ' ' . $value;
                } else {
                    Error::showError('只能为正序或倒序');
                }
            }

            return $prefix . ' ' . implode(',', $orders);
        } else {
            return $prefix . ' ' . $order;
        }
    }

    public function orderby($order)
    {
        $this->select['orderby'] = $this->convertOrder($order);
        return $this;
    }

    public function order($order)
    {
        $this->orderby($order);
        return $this;
    }

    public function having($mix)
    {
        $prefix = 'HAVING';
        $having = '';
        if (is_array($mix)) {
            foreach ($mix as $key => $value) {
                $having .= strtoupper($key) . $this->escapeValue($value);
            }
        } else {
            Error::showError('having 参数必须为数组');
        }

        $this->select['having'] = $prefix . ' ' . $having;
        return $this;
    }

    public function chunk($size, $func, $order = 'asc', $counter_break = '')
    {
        $table = $this->getTable();
        $times = 1;
        $this->orderby([$this->key => $order]);
        $rs = $this->limit([$size])->get(['chunk']);

        $options = $this->select;
        $column = $this->getSelectColumn($this->select['select']);
        $fields = explode(',', $column);
        if (!in_array($this->key, $fields)) {
            Error::showError('chunk方法查询字段必须带主键');
        }

        $where = str_replace('WHERE', '', ($this->wheres ?: $this->getBindWhere()) ?: 'WHERE 1');
        $binds = $this->bind;
        while ($rs) {
            if (false === call_user_func($func, $rs)) {
                return false;
            }

            $times++;
            if ($counter_break && $times >= $counter_break) {
                return false;
            }

            $last_rs = end($rs);
            $opera = ($order == 'asc') ? '>' : '<';
            $this->bind_where = [$where . ' AND ' . $this->key . $opera . $last_rs[$this->key]];
            $this->bind = $binds;
            $options['limit'] = ' LIMIT ' . $size;
            $sql_array = $this->sortSql($options);
            $sql = $this->buildSql($sql_array);
            $rs = $this->query($sql, ['chunk']);
        }

        return true;
    }

    public function toSql()
    {
        $sql = $this->buildSql();
        return $this->getLastQuery($sql, $this->bind);
    }

    public function sortSql($options)
    {
        $sorts = ['select', 'from', 'join', 'left join', 'inner join', 'right join', 'outter join', 'where', 'groupby', 'having', 'orderby', 'limit'];
        $rs = [];
        foreach ($sorts as $value) {
            if (isset($options[$value])) {
                $rs[$value] = $options[$value];
            }
        }

        return $rs;
    }

    public function groupby($field)
    {
        $this->select['groupby'] = 'GROUP BY ' . $this->escapeColumn($field);
        return $this;
    }

    public function join($table, $on, $type = 'left')
    {
        $join = $type . ' join';
        $this->select[$join][] = $join . ' ' . $this->getTable($table) . ' ON ' . $on;
        return $this;
    }

    public function count($page = '')
    {
        $params = $this->select;
        $field = $page ? '*' : ($this->getSelectColumn() ?: '*');
        $params['select'] = 'SELECT COUNT(' . $field . ')';
        unset($params['limit'], $params['orderby']);
        $sql = $this->buildSql($params);

        return $page ? $this->query($sql, ['page', 'COUNT(*)']) : $this->query($sql, ['value', 'COUNT(*)']);
    }

    public function page($per_page, $current_page = '', $show_link = false)
    {
        $per_page = intval($per_page);
        $total = $this->count(true);
        $page = Pagination::getClass([
            'total' => $total, // 总条数
            'per_page' => $per_page, // 每页显示的条数
        ]);
        $current = $current_page ?: $page->getPage();
        $start = $per_page * ($current - 1);
        if ($show_link) {
            $data['links'] = $page->links();
        }

        $data['total'] = $total;
        $data['rs'] = ($total > 0) ? $this->limit(intval($start), intval($per_page))->get() : [];

        return $data;
    }
}
