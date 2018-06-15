<?php

class Charset {
  private static $funcOverload; 

  public static function init() {
    ini_set('default_charset', 'UTF-8');

    if (extension_loaded('mbstring')) {
      define('MB_ENABLED', true);
      @ini_set('mbstring.internal_encoding', 'UTF-8');
      mb_substitute_character('none');
    } else {
      define('MB_ENABLED', false);
    }

    if (extension_loaded('iconv')) {
      define('ICONV_ENABLED', true);
      @ini_set('iconv.internal_encoding', 'UTF-8');
    } else {
      define('ICONV_ENABLED', false);
    }

    ini_set('php.internal_encoding', 'UTF-8');

    Load::path(PATH_SYS . 'compatible' . DIRECTORY_SEPARATOR . 'MbString.php');

    self::$funcOverload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));


    define('UTF8_ENABLED', defined('PREG_BAD_UTF8_ERROR') && (ICONV_ENABLED === true || MB_ENABLED === true));
  }

  public static function strlen($str) {
    return self::$funcOverload ? mb_strlen($str, '8bit') : strlen($str);
  }

  public static function substr($str, $start, $length = null) {
    if (self::$funcOverload) {
      isset($length) || $length =($start >= 0 ? self::strlen($str) - $start : -$start);
      return mb_substr($str, $start, $length, '8bit');
    }

    return isset($length) ? substr($str, $start, $length) : substr($str, $start);
  }

  public static function cleanString($str) {
    return self::isAscii($str) === false ? !MB_ENABLED ? ICONV_ENABLED ? @iconv('UTF-8', 'UTF-8//IGNORE', $str) : $str : mb_convert_encoding($str, 'UTF-8', 'UTF-8') : $str;
  }

  public static function isAscii($str) {
    return preg_match('/[^\x00-\x7F]/S', $str) === 0;
  }
}