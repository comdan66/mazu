<?php

// 導頁
// 整理

class Url {
  private static $segments;
  private static $baseUrl;

  public static function init () {
    self::$baseUrl = config('other', 'base_url');
    self::$segments = array_map(function ($t) {
      return urldecode($t);
    }, isCli() ? self::parseArgv() : self::parseRequestUri());
  }

  private static function parseRequestUri() {
    return ($tmp = parse_url('http://__' . $_SERVER['REQUEST_URI'])) && isset($tmp['path']) ? array_filter(explode('/', $tmp['path']), function($t) {
      return $t !== '';
    }) : [];
  }

  private static function parseArgv() {
    return arr2dTo1d(array_map(function($argv) {
      return explode('/', $argv);
    }, array_slice($_SERVER['argv'], 1)));
  }

  public static function base() {
    $baseUrl =& self::$baseUrl;
    $baseUrl || (isset($_SERVER['HTTP_HOST']) && ($baseUrl =(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http') . '://'. $_SERVER['HTTP_HOST'] . '/')) || gg('尚未設定 base_url');
    $baseUrl = rtrim($baseUrl, '/') . '/';

    $args = trim(preg_replace('/\/+/', '/', implode('/', arr2dTo1d(func_get_args()))), '/');
    return $baseUrl . $args;
  }

  public static function current() {
    return self::base(self::$segments);
  }

  public static function segments() {
    return self::$segments;
  }

  public static function refresh () {
    if (!$args = func_get_args())
      return false;

    if (is_string($args[0]) && preg_match('/^(http|https):\/{2}/', $args[0], $matches))
      return header('Refresh:0;url=' . $args[0]);

    if (!$args = array_filter(explode('/', (trim(preg_replace('/\/+/', '/', implode('/', arr2dTo1d($args))), '/'))), function ($t) { return $t !== ''; }))
      return false;


    header('Refresh:0;url=' . self::base($args));
    exit;
  }

  public static function redirect ($code = 302) {
    if (!$args = func_get_args ())
      return false;

    $code = array_shift ($args);
    $code = !is_numeric ($code) ? isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1' ? $_SERVER['REQUEST_METHOD'] !== 'GET' ? 303 : 307 : 302 : $code;

    if (is_string($args[0]) && preg_match('/^(http|https):\/{2}/', $args[0], $matches))
      return header('Location: ' . $args[0], true, $code);

    if (!$args = array_filter(explode('/', (trim(preg_replace('/\/+/', '/', implode('/', arr2dTo1d($args))), '/'))), function ($t) { return $t !== ''; }))
      return false;

    header('Location: ' . self::base($args), true, $code);
    exit;
  }
}

Url::init();