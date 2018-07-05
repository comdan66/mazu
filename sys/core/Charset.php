<?php defined('MAZU') || exit('此檔案不允許讀取！');

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
Load::sysCore('MbString.php') || gg('載入 MbString 失敗！');

define('UTF8_ENABLED', defined('PREG_BAD_UTF8_ERROR') && (ICONV_ENABLED === true || MB_ENABLED === true));
define('FUNC_OVERLOAD', extension_loaded('mbstring') && ini_get('mbstring.func_overload'));

if (!function_exists('charsetStrlen')) {
  function charsetStrlen($str) {
    return FUNC_OVERLOAD ? mb_strlen($str, '8bit') : strlen($str);
  }
}

if (!function_exists('charsetSubstr')) {
  function charsetSubstr($str, $start, $length = null) {
    if (FUNC_OVERLOAD) {
      isset($length) || $length =($start >= 0 ? charsetStrlen($str) - $start : -$start);
      return mb_substr($str, $start, $length, '8bit');
    }

    return isset($length) ? substr($str, $start, $length) : substr($str, $start);
  }
}
if (!function_exists('charsetCleanStr')) {
  function charsetCleanStr($str) {
    return (preg_match('/[^\x00-\x7F]/S', $str) === 0) === false ? !MB_ENABLED ? ICONV_ENABLED ? @iconv('UTF-8', 'UTF-8//IGNORE', $str) : $str : mb_convert_encoding($str, 'UTF-8', 'UTF-8') : $str;
  }
}