<?php

namespace _M;

use PDO;
use PDOException;
use Closure;

abstract class Connection {
  const DATETIME_TRANSLATE_FORMAT = 'Y-m-d H:i:s';


  // public $last_query;

  // private $log;
 
  // public $protocol;
  
  // static $date_format = 'Y-m-d';

  // static $datetime_format = 'Y-m-d H:i:s';
  

  
  // static $DEFAULT_PORT = 0;
  private static $instance = null;
  public static $pdoOptions = [PDO::ATTR_CASE => PDO::CASE_LOWER, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, PDO::ATTR_STRINGIFY_FETCHES => false];
  public static $quoteCharacter = '`';

  private $connection = null;

  protected function __construct($host, $db, $user, $pass) {
    try {

      $this->connection = new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass, Connection::$pdoOptions);
    } catch (PDOException $e) {
      gg($e);
    }
  }

  public static function create($host, $db, $user, $pass) {
    return new static($host, $db, $user, $pass);
  }

  public static function instance() {
    if (self::$instance)
      return self::$instance;

      $host = '127.0.0.1';
      $db = 'gps.kerker';
      $user = 'root';
      $pass = '1234';
      $charset = 'utf8mb4';
      // Config...

    return self::$instance = MysqlAdapter::create($host, $db, $user, $pass)->setEncoding($charset);
  }
  
  public function query($sql, &$vals = []) {
    try {
      $sth = $this->connection->prepare($sql);

      $sth || gg('Connection prepare failure!');
    } catch (PDOException $e) {
      gg($e);
    }

    $sth->setFetchMode(PDO::FETCH_ASSOC);

    try {
      $this->execute ($sth, $sql, $vals) || gg('Connection execute failure!');
    } catch (PDOException $e) {
      gg($e);
    }

    return $sth;
  }

  private function execute ($sth, $sql, $vals) {
    // if (!$log = $this->log)
    //   return $sth->execute ($vals);

    $start = microtime (true);
    $valid = $sth->execute ($vals);
    $time = number_format ((microtime (true) - $start) * 1000, 1);

    // $log::query ($valid ? true : false, $time, $sql, $vals);
    return $valid;
  }

  public function quoteName($string) {
    return $string[0] === static::$quoteCharacter || $string[strlen($string) - 1] === static::$quoteCharacter ? $string : static::$quoteCharacter . $string . static::$quoteCharacter;
  }


  public function columns($table) {

    $columns = [];
    $sth = $this->queryColumnInfo($table);
    
    while ($row = $sth->fetch()) {
      $c = $this->createColumn($row);
      $columns[$c->name] = $c;
    }
    
    return $columns;
  }

  public function stringTodatetime($string) {
    $date = date_create($string);
    $errors = \DateTime::getLastErrors();

    if ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
      return null;

    // $date_class = Config::instance()->get_date_class();

    // return $date_class::createFromFormat(
    //   static::DATETIME_TRANSLATE_FORMAT,
    //   $date->format(static::DATETIME_TRANSLATE_FORMAT),
    //   $date->getTimezone()
    // );
  }

  abstract public function queryColumnInfo($table);
  abstract function setEncoding($charset);

  //   $config = Config::instance();

  //   if (strpos($connection_string_or_connection_name, '://') === false)
  //   {
  //     $connection_string = $connection_string_or_connection_name ?
  //       $config->get_connection($connection_string_or_connection_name) :
  //       $config->get_default_connection_string();
  //   }
  //   else
  //     $connection_string = $connection_string_or_connection_name;

  //   if (!$connection_string)
  //     throw new DatabaseException("Empty connection string");

  //   $fqclass = static::load_adapter_class($info->protocol);


  // private static function load_adapter_class($adapter)
  // {
  //   $class = ucwords($adapter) . 'Adapter';
  //   $fqclass = 'ActiveRecord\\' . $class;
  //   $source = __DIR__ . "/adapters/$class.php";

  //   if (!file_exists($source))
  //     throw new DatabaseException("$fqclass not found!");

  //   require_once($source);
  //   return $fqclass;
  // }



  // public function escape($string)
  // {
  //   return $this->connection->quote($string);
  // }

  // public function insert_id($sequence=null)
  // {
  //   return $this->connection->lastInsertId($sequence);
  // }


  // public function query_and_fetch_one($sql, &$values=array())
  // {
  //   $sth = $this->query($sql, $values);
  //   $row = $sth->fetch(PDO::FETCH_NUM);
  //   return $row[0];
  // }

  // public function query_and_fetch($sql, Closure $handler)
  // {
  //   $sth = $this->query($sql);

  //   while (($row = $sth->fetch(PDO::FETCH_ASSOC)))
  //     $handler($row);
  // }

  // public function tables()
  // {
  //   $tables = array();
  //   $sth = $this->query_for_tables();

  //   while (($row = $sth->fetch(PDO::FETCH_NUM)))
  //     $tables[] = $row[0];

  //   return $tables;
  // }

  // public function transaction()
  // {
  //   if (!$this->connection->beginTransaction())
  //     throw new DatabaseException($this);
  // }

  // public function commit()
  // {
  //   if (!$this->connection->commit())
  //     throw new DatabaseException($this);
  //   return true;
  // }

  // public function rollback()
  // {
  //   if (!$this->connection->rollback())
  //     throw new DatabaseException($this);
  //   return true;
  // }

  // function supports_sequences()
  // {
  //   return false;
  // }

  // public function get_sequence_name($table, $column_name)
  // {
  //   return "{$table}_seq";
  // }

  // public function next_sequence_value($sequence_name)
  // {
  //   return null;
  // }


  // public function date_to_string($datetime)
  // {
  //   return $datetime->format(static::$date_format);
  // }

  // public function datetime_to_string($datetime)
  // {
  //   return $datetime->format(static::$datetime_format);
  // }


  // abstract function limit($sql, $offset, $limit);


  // abstract function query_for_tables();


  // abstract public function native_database_types();

  // public function accepts_limit_and_order_for_update_and_delete()
  // {
  //   return false;
  // }
}
