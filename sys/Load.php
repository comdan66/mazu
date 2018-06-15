<?php

class Load {
  private static $cache = array ();

  public static function path($path) {
    if (!empty(self::$cache[$path]))
      return true;

    is_file($path) && is_readable($path) || gg('載入檔案失敗。');

    require_once $path;

    return self::$cache[$path] = true;
  }
}