<?php

namespace _M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Config {

  const DATE_FORMAT = 'Y-m-d';
  const DATETIME_FORMAT = 'Y-m-d H:i:s';
  const QUOTE_CHART = '`';
  
  private static $modelsDir = null;
  private static $queryLogFunc = null;
  private static $logFunc = null;
  private static $errorFunc = null;
  private static $connection = null;

  public static function quoteName($string) {
    return $string[0] === Config::QUOTE_CHART || $string[strlen($string) - 1] === Config::QUOTE_CHART ? $string : Config::QUOTE_CHART . $string . Config::QUOTE_CHART;
  }

  public static function setModelsDir($modelsDir) {
    is_dir($modelsDir) && is_readable($modelsDir) && self::$modelsDir = rtrim($modelsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }

  public static function setQueryLogFunc($queryLogFunc) {
    is_callable($queryLogFunc) && self::$queryLogFunc = $queryLogFunc;
  }

  public static function setLogFunc($logFunc) {
    is_callable($logFunc) && self::$logFunc = $logFunc;
  }
  
  public static function setErrorFunc($errorFunc) {
    is_callable($errorFunc) && self::$errorFunc = self::$errorFunc = $errorFunc;
  }

  public static function setConnection($connection) {
    $connection && is_array($connection) && self::$connection = $connection;
  }








  
  

  public static function noQueryLogFunc() {
    return self::$queryLogFunc === null;
  }

  public static function getModelsDir() {
    return self::$modelsDir;
  }

  public static function getConnection() {
    return self::$connection;
  }

  public static function log() {
    ($func = self::$logFunc) && call_user_func_array($func, func_get_args());
  }

  public static function queryLog() {
    ($func = self::$queryLogFunc) && call_user_func_array($func, func_get_args());
  }

  public static function error($error) {
    ($func = self::$errorFunc) && call_user_func_array($func, [null, 500, ['msgs' => func_get_args()]]) || exit($error);
  }
}


if (!function_exists('autoloadModel')) {
  function autoloadModel($className) {
    if (!(($namespaces = \M\getNamespaces($className)) && in_array($namespace = array_shift($namespaces), ['M', '_M']) && ($modelName = \M\deNamespace($className))))
      return false;

    $path = ($namespace == 'M' ? \_M\Config::getModelsDir() : __DIR__ . DIRECTORY_SEPARATOR) . $modelName . '.php';

    if (!is_readable($path))
      return false;

    require_once $path;

    class_exists($className) || \_M\Config::error('找不到 Model 名稱為「' . $className . '」的物件。');
  }
  
  spl_autoload_register('\_M\autoloadModel', false, true);
}
