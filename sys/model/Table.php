<?php

namespace _M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Table {
  private static $caches = [];

  private $className;

  public $tableName;
  public $columns;
  public $primaryKeys;

  protected function __construct($className) {
    $this->setTableName($className)
         ->getMetaData()
         ->setPrimaryKeys();
  }

  private function setTableName($className){
    $this->className = $className;
    $this->tableName = isset($className::$tableName) ? $className::$tableName : \M\deNamespace($className);
    return $this;
  }

  private function getMetaData() {
    $this->columns = [];
    $sth = Connection::instance()->query("SHOW COLUMNS FROM " . Config::quoteName($this->tableName));

    foreach ($sth->fetchAll() as $row)
      if ($column = Table::column($row, $this->className))
        $this->columns[$column['name']] = $column;

    return $this;
  }

  public static function column($row, $className) {
    $row = array_change_key_case($row, CASE_LOWER);

    if ($row['type'] == 'timestamp' || $row['type'] == 'datetime') {
      $type = 'datetime';
    } elseif ($row['type'] == 'date') {
      $type = 'date';
    } elseif ($row['type'] == 'time') {
      $type = 'time';
    } else {
      preg_match('/^([A-Za-z0-9_]+)(\(([0-9]+(,[0-9]+)?)\))?/', $row['type'], $matches);
      $type = (count($matches) > 0 ? $matches[1] : $row['type']);
    }

    $type == 'integer' && $this->type = 'int';

    return [
      'name' => $row['field'],
      'null' => $row['null'] === 'YES', // 是否可為 null
      'pk' => $row['key'] === 'PRI', // 是否為主鍵
      'ai' => $row['extra'] === 'auto_increment', // 是否自動增加
      'type' => $type,
      'd4' => $row['default'],
    ];
  }

  private function setPrimaryKeys() {
    $className = $this->className;
    $this->primaryKeys = isset($className::$primaryKeys) ? is_array($className::$primaryKeys) ? $className::$primaryKeys : [$className::$primaryKeys] : \array_column(array_values(array_filter($this->columns, function ($column) { return $column['pk']; })), 'name');
    return $this;
  }

  public static function instance($className) {
    return isset(self::$caches[$className]) ? self::$caches[$className] : self::$caches[$className] = new Table($className);
  }
 
  public function find($options) {
    $sql = SqlBuilder::create(Config::quoteName($this->tableName))
                     ->setSelectOption($options);

    return $this->findBySql($sql, $sql->getValues(), isset($options['readonly']) ? (bool)$options['readonly'] : false, isset($options['include']) ? is_string($options['include']) ? [$options['include']] : $options['include'] : []);
  }

  public function processDataToStr($data) {
    foreach ($data as $name => &$value)
      if ($value instanceof DateTime)
        $value = $value->format(null, null);
      else
        $value = $value;

    return $data;
  }

  private function mergeWherePrimaryKeys($primaryKeys) {
    $where = \Where::create();
    foreach ($primaryKeys as $primaryKey => $value)
      $where->and($primaryKey . ' = ?', $value);

    return $where;
  }

  public function delete($primaryKeys) {
    $data = $this->processDataToStr($primaryKeys);
    $where = $this->mergeWherePrimaryKeys($primaryKeys);

    $sql = SqlBuilder::create(Config::quoteName($this->tableName))
                     ->delete()
                     ->where($where->toArray())
                     ->bindValues();
    
    return Connection::instance()->query($sql, $sql->getValues());
  }

  public function insert($data) {
    $data = $this->processDataToStr($data);

    $sql = SqlBuilder::create(Config::quoteName($this->tableName))
                     ->insert($data);

    return Connection::instance()->query($sql, array_values($data));
  }

  public function update($data, $primaryKeys) {
    $data = $this->processDataToStr($data);
    $where = $this->mergeWherePrimaryKeys($primaryKeys);

    $sql = SqlBuilder::create(Config::quoteName($this->tableName))
                     ->update($data)
                     ->where($where->toArray())
                     ->bindValues();

    return Connection::instance()->query($sql, $sql->getValues());
  }

  public function findBySql($sql, $values = [], $readonly = false, $includes = []) {
    $sth = Connection::instance()->query($sql, $values);
    $tableName = $this->tableName;
    $className = $this->className;
    
    $objs = array_map(function ($row) use ($tableName, $className, $readonly) {
      $obj = new $this->className($row);

      return $obj->setIsNew(false)
                 ->setTableName($tableName)
                 ->setClassName($className)
                 ->setIsReadonly($readonly)
                 ->setUploadBind();
    }, $sth->fetchAll());

    foreach ($includes as $name => $include) {
      if (is_numeric($name)) {
        $name = $include;
        $include = [];

        if (($i = strpos($name, '.')) !== false) {
          $tmp = substr($name, $i + 1);
          $name = substr($name, 0, $i);
          $include = ['include' => $tmp];
        }
      }

      foreach (['hasOne', 'hasMany', 'belongToOne', 'belongToMany'] as $val)
        if (isset($className::$$val) && ($tmp = $className::$$val) && isset($tmp[$name]))
          $className::relations($val, array_merge($tmp[$name], $include), $objs, $name);
    }

    return $objs;
  }
}