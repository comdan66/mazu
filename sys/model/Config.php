<?php

namespace _M;

require_once 'Where.php';

class Config {

  const DATE_FORMAT = 'Y-m-d';
  const DATETIME_FORMAT = 'Y-m-d H:i:s';
  
  private static $modelsDir = null;
  private static $queryLogerFunc = null;
  private static $logerFunc = null;
  private static $connection = [];
  private static $errorFunc = null;

  public static $quoteCharacter = '`';

  public static function quoteName($string) {
    return $string[0] === static::$quoteCharacter || $string[strlen($string) - 1] === static::$quoteCharacter ? $string : static::$quoteCharacter . $string . static::$quoteCharacter;
  }

  public static function setModelsDir($modelsDir) {
    is_dir($modelsDir) && is_readable($modelsDir) && self::$modelsDir = rtrim($modelsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }

  public static function setLogerFunc($logerFunc) {
    is_callable($logerFunc) && self::$logerFunc = $logerFunc;
  }
  
  public static function setQueryLogerFunc($queryLogerFunc) {
    is_callable($queryLogerFunc) && self::$queryLogerFunc = $queryLogerFunc;
  }
  
  public static function setErrorFunc($errorFunc) {
    is_callable($errorFunc) && \Where::$errorFunc = self::$errorFunc = $errorFunc;
  }
  
  public static function setConnection($connection) {
    $connection && is_array($connection) && self::$connection = $connection;
  }

  public static function getModelsDir() {
    return self::$modelsDir;
  }

  public static function noQueryLogerFunc() {
    return self::$queryLogerFunc === null;
  }

  public static function getConnection() {
    return self::$connection;
  }

  public static function log() {
    ($func = self::$logerFunc) && call_user_func_array($func, func_get_args());
  }

  public static function queryLog() {
    ($func = self::$queryLogerFunc) && call_user_func_array($func, func_get_args());
  }

  public static function error($error) {
    ($func = self::$errorFunc) && call_user_func_array($func, func_get_args()) || exit($error);
  }

  public static function __autoloadModel($className) {
    if (!(($namespaces = \M\getNamespaces($className)) && in_array($namespace = array_shift($namespaces), ['M', '_M']) && ($modelName = \M\deNamespace($className))))
      return false;

    $path = ($namespace == 'M' ? \_M\Config::getModelsDir() : __DIR__ . DIRECTORY_SEPARATOR) . $modelName . '.php';

    if (!is_readable($path))
      return false;

    require_once $path;

    class_exists($className) || \_M\Config::error('找不到 Model 名稱為「' . $className . '」的物件。');
  }
}

spl_autoload_register(['\_M\Config', '__autoloadModel'], false, true);