<?php

if (!function_exists('load')) {
  function load($path, $error = null) {
    static $cache;
    
    if (isset($cache[$path]))
      return true;

    $error || $error = '載入檔案「' . $path . '」失敗！';

    is_file($path) && is_readable($path) || (is_callable('gg') ? gg($error) : exit($error));

    require_once $path;

    return $cache[$path] = true;
  }
}