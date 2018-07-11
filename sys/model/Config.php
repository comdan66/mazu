<?php

namespace _M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Config {
  const FORMAT_DATE = 'Y-m-d';
  const FORMAT_DATETIME = 'Y-m-d H:i:s';
  const QUOTE = '`';
  
  private static $connection = null;

  public static function quoteName($string) {
    return $string[0] === Config::QUOTE || $string[strlen($string) - 1] === Config::QUOTE ? $string : Config::QUOTE . $string . Config::QUOTE;
  }

  public static function setConnection($connection) {
    $connection && is_array($connection) && self::$connection = $connection;
  }

  public static function getConnection() {
    return self::$connection;
  }
}
