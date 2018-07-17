<?php

namespace _M;

defined('MAZU') || exit('此檔案不允許讀取！');

use PDO;
use PDOException;

class Connection {
  private static $instance = null;
  private $connection = null;
  public static $pdoOptions = [
    PDO::ATTR_CASE => PDO::CASE_NATURAL,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
    PDO::ATTR_STRINGIFY_FETCHES => false
  ];

  protected function __construct() {
    $config = Config::getConnection();
    $config || \gg('沒有設定 MySQL 連線資訊！');
    
    foreach (['hostname', 'username', 'password', 'database', 'encoding'] as $key)
      isset($config[$key]) || \gg('MySQL 連線資訊缺少「' . $key . '」！');

    try {
      $this->connection = new PDO('mysql:host=' . $config['hostname'] . ';dbname=' . $config['database'], $config['username'], $config['password'], Connection::$pdoOptions);
    } catch (PDOException $e) {
      \gg('PDO 連線錯誤！', $e);
    }

    $this->setEncoding($config['encoding']);
  }

  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new Connection();
  }

  public function close() {
    $this->connection = null;
  }

  public function setEncoding($encoding) {
    $this->query('SET NAMES ?', [$encoding]);
    return $this;
  }
  
  public function query($sql, $vals = [], $fetchModel = PDO::FETCH_ASSOC, $returnError = false) {

    try {
      $sth = $this->connection->prepare((string)$sql);
      $sth || \gg('執行 Connection prepare 失敗！');
      $sth->setFetchMode($fetchModel);
      $this->execute($sth, $sql, $vals) || \gg('執行 Connection execute 失敗！');
    } catch (PDOException $e) {
      if ($returnError)
        return $e->getMessage();

      \gg('PDO 執行失敗！', $e);
    }

    return $returnError ? '' : $sth;
  }

  private function execute($sth, $sql, $vals) {

    $start = microtime(true);
    $valid = $sth->execute($vals);
    $time = number_format((microtime(true) - $start) * 1000, 1);
    \Log::query((bool)$valid, $time, $sql, $vals);

    return $valid;
  }

  public function transaction() {
    if (!$this->connection) return false;
    $this->connection->beginTransaction() || \gg('Transaction 失敗！');
    return true;
  }

  public function commit() {
    if (!$this->connection) return false;
    $this->connection->commit() || \gg('Commit 失敗！');
    return true;
  }

  public function rollback() {
    if (!$this->connection) return false;
    $this->connection->rollback() || \gg('Rollback 失敗！');
    return true;
  }

  public function lastInsertId() {
    return $this->connection->lastInsertId();
  }
}
