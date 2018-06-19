<?php
namespace _M;

class Column {
  const STRING    = 1;
  const INTEGER   = 2;
  const DECIMAL   = 3;
  const DATETIME  = 4;
  const DATE      = 5;
  const TIME      = 6;

  static $typeMapping = [
    'datetime'  => self::DATETIME,
    'timestamp' => self::DATETIME,
    'date'      => self::DATE,
    'time'      => self::TIME,

    'tinyint'   => self::INTEGER,
    'smallint'  => self::INTEGER,
    'mediumint' => self::INTEGER,
    'int'       => self::INTEGER,
    'bigint'    => self::INTEGER,

    'float'     => self::DECIMAL,
    'double'    => self::DECIMAL,
    'numeric'   => self::DECIMAL,
    'decimal'   => self::DECIMAL,
    'dec'       => self::DECIMAL];

  public $name;
  // public $inflected_name;
  public $type;
  public $rawType;
  public $length;
  public $nullable;
  public $primaryKey;
  public $default;
  public $autoIncrement;

  public static function castIntegerSafely($val) {
    if (is_int($val))
      return $val;
    elseif (is_numeric($val) && floor($val) != $val)
      return (int) $val;
    elseif (is_string($val) && is_float($val + 0))
      return (string) $val;
    elseif (is_float($val) && $val >= PHP_INT_MAX)
      return number_format($val, 0, '', '');
    else
      return (int) $val;
  }

  public function cast($val) {
    if ($val === null)
      return null;

    switch ($this->type) {
      case self::STRING:
        return (string)$val;
      
      case self::INTEGER:
        return static::castIntegerSafely($val);
      
      case self::DECIMAL:
        return (double)$val;
      
      case self::DATETIME: case self::DATE:
        if (!$val)
          return null;

        if ($val instanceof \DateTime)
          return $dateClass::createFromFormat(Connection::DATETIME_TRANSLATE_FORMAT, $val->format(Connection::DATETIME_TRANSLATE_FORMAT), $val->getTimezone());
    
        $val = date_create($val);
        $errors = \DateTime::getLastErrors();

        return $errors['warning_count'] || $errors['error_count'] ? null : $val;
    }
    return $val;
  }

  public function mapRawType() {
    $this->rawType == 'integer' && $this->rawType = 'int';
    return $this->type = isset(self::$typeMapping[$this->rawType]) ? self::$typeMapping[$this->rawType] : self::STRING;
  }
}


class MysqlAdapter extends Connection {
  private static $port = 3306;

  public function setEncoding($charset) {
    $params = [$charset];
    $this->query('SET NAMES ?', $params);
    return $this;
  }

  public function queryColumnInfo($table) {
    return $this->query("SHOW COLUMNS FROM " . $table);
  }

  public function createColumn($row) {
    $column = new Column();
    $row = array_change_key_case($row, CASE_LOWER);

    $column->name          = $row['field'];
    $column->nullable      = $row['null'] === 'YES';
    $column->primaryKey    = $row['key'] === 'PRI';
    $column->autoIncrement = $row['extra'] === 'auto_increment';

    if ($row['type'] == 'timestamp' || $row['type'] == 'datetime') {
      $column->rawType = 'datetime';
      $column->length  = 19;
    } elseif ($row['type'] == 'date') {
      $column->rawType = 'date';
      $column->length  = 10;
    } elseif ($row['type'] == 'time') {
      $column->rawType = 'time';
      $column->length  = 8;
    } else {
      preg_match('/^([A-Za-z0-9_]+)(\(([0-9]+(,[0-9]+)?)\))?/', $row['type'], $matches);
      $column->rawType = (count($matches) > 0 ? $matches[1] : $row['type']);
      count($matches) < 3 || $column->length = intval($matches[3]);
    }

    $column->mapRawType();
    $column->default = $column->cast($row['default']);

    return $column;
  }




  // public function query_for_tables()
  // {
  //   return $this->query('SHOW TABLES');
  // }



  // public function accepts_limit_and_order_for_update_and_delete() { return true; }

  // public function native_database_types()
  // {
  //   return array(
  //     'primary_key' => 'int(11) UNSIGNED DEFAULT NULL auto_increment PRIMARY KEY',
  //     'string' => array('name' => 'varchar', 'length' => 255),
  //     'text' => array('name' => 'text'),
  //     'integer' => array('name' => 'int', 'length' => 11),
  //     'float' => array('name' => 'float'),
  //     'datetime' => array('name' => 'datetime'),
  //     'timestamp' => array('name' => 'datetime'),
  //     'time' => array('name' => 'time'),
  //     'date' => array('name' => 'date'),
  //     'binary' => array('name' => 'blob'),
  //     'boolean' => array('name' => 'tinyint', 'length' => 1)
  //   );
  // }

}
