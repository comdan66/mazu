<?php

// if (!function_exists('load')) {
//   function load($path, $error = null) {
//     static $cache;
    
//     if (isset($cache[$path]))
//       return true;

//     $error || $error = '載入檔案「' . $path . '」失敗！';

//     if (!(is_file($path) && is_readable($path)))
//       return false;

//     require_once $path;

//     return $cache[$path] = true;
//   }
// }

class Load {
  private static $cache = [];

  public static function path($path, $error = null) {
    if (isset(self::$cache[$path]))
      return true;

    $error || $error = '載入檔案「' . $path . '」失敗！';

    if (!(is_file($path) && is_readable($path)))
      return false;

    require_once $path;

    return self::$cache[$path] = true;
  }
}