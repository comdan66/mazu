<?php

namespace _M;

class SqlBuilder {
  private $quoteTableName;
  private $toStringFunc = null;
  private $select = '*';
  private $where, $order, $limit, $offset, $group, $having, $values, $data;

  public function __construct($quoteTableName) {
    $this->quoteTableName = $quoteTableName;

    $this->toStringFunc = 'buildSelect';
    $this->select = '*';
    $this->where = null;
    $this->order = null;
    $this->limit = 0;
    $this->offset = 0;
    $this->group = null;
    $this->having = null;
    $this->values = [];
  }

  public static function create($quoteTableName) {
    return new static($quoteTableName);
  }

  public function select($select) {
    if ($select === null)
      return $this;

    $this->toStringFunc = 'buildSelect';
    $this->select = $select ? $select : '*';
    return $this;
  }

  public function where($where) {
    if ($where === null)
      return $this;

    $whereStr = array_shift($where);

    $i = 0;
    foreach ($where as &$value)
      if (is_array($value)) {
        ($i = strpos($whereStr, '(?)', $i)) === false && Config::error('Where 格式有誤！', '條件：'. $whereStr, '參數：' . implode(',', $value));
        $whereStr = substr($whereStr, 0, $i) . '(' . ($value ? implode(',', array_map(function () { return '?'; }, $value)) : '?') . ')' . substr($whereStr, $i += 3);
        $value = $value ? $value : 'null';
      }

    $where = \M\arrayFlatten($where);
    substr_count($whereStr, '?') == count($where) || Config::error('Where 格式有誤！', '條件：' . $whereStr, '參數：' . implode(',', $where));

    $this->where = $whereStr;
    $this->values = $where;

    return $this;
  }

  public function order($order) {
    $this->order = (string)$order;
    return $this;
  }

  public function limit($limit) {
    $this->limit = intval($limit);
    return $this;
  }

  public function offset($offset) {
    $this->offset = intval($offset);
    return $this;
  }

  public function group($group) {
    if ($group === null)
      return $this;

    $this->group = $group;
    return $this;
  }

  public function having($having) {
    if ($having === null)
      return $this;

    $this->having = $having;
    return $this;
  }

  public function setSelectOption($options) {
    foreach (['select', 'where', 'order', 'limit', 'offset', 'group', 'having'] as $method)
      isset($options[$method]) && $this->$method($options[$method]);
    return $this;
  }

  public function __toString() {
    return $this->toString();
  }

  public function toString() {
    $func = $this->toStringFunc;
    return $this->$func();
  }

  private function buildSelect() {
    $sql = "SELECT " . $this->select . " FROM " . $this->quoteTableName;

    $this->where  && $sql .= ' WHERE ' . $this->where;
    $this->group  && $sql .= ' GROUP BY ' . $this->group;
    $this->having && $sql .= ' HAVING ' . $this->having;
    $this->order  && $sql .= ' ORDER BY ' . $this->order;
    
    if ($this->limit || $this->offset)
      $sql .= ' LIMIT ' . intval($this->offset) . ', ' . intval($this->limit);

    return $sql;
  }

  public function getValues() {
    return $this->values;
  }
  
  private function buildUpdate() {
    $set = implode('=?, ', array_map(function ($t) { return Config::quoteName($t); }, array_keys($this->data))) . '=?';
    $sql = "UPDATE " . $this->quoteTableName . " SET " . $set;
    $this->where && $sql .= " WHERE " . $this->where;

    return $sql;
  }

  public function update($data) {
    $this->toStringFunc = 'buildUpdate';
    $this->data = $data;
    return $this;
  }

  public function bindValues() {
    $ret = [];

    if ($this->data)
      $ret = array_values($this->data);

    if ($this->values)
      $ret = array_merge($ret, $this->values);

    $this->values = \M\arrayFlatten($ret);
    
    return $this;
  }

  public function delete($data = []) {
    $this->toStringFunc = 'buildDelete';
    $this->data = $data;
    return $this;
  }

  public function buildDelete() {
    $sql = "DELETE FROM " . $this->quoteTableName;
    $this->where && $sql .= " WHERE " . $this->where;

    return $sql;
  }

  public function buildInsert() {
    $keys = array_map(function ($t) { return Config::quoteName($t); }, array_keys($this->data));
    $sql = "INSERT INTO " . $this->quoteTableName . "(" . implode(', ', $keys) . ") VALUES(" . implode(', ', array_map(function () { return '?'; }, $keys)) . ")";
    return $sql;
  }

  public function insert($data, $pk = null, $sequence_name = null) {
    $this->toStringFunc = 'buildInsert';
    $this->data = $data;
    return $this;
  }
}