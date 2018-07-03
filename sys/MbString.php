<?php defined('MAZU') || exit('此檔案不允許讀取！');

if (MB_ENABLED === true)
  return;

if (!function_exists('mb_strlen')) {
  function mb_strlen($str, $encoding = 'UTF-8') {
    return ICONV_ENABLED !== true ? strlen($str) : iconv_strlen($str, $encoding);
  }
}

if (!function_exists('mb_strpos')) {
  function mb_strpos($haystack, $needle, $offset = 0, $encoding = 'UTF-8') {
    return ICONV_ENABLED !== true ? strpos($haystack, $needle, $offset) : iconv_strpos($haystack, $needle, $offset, $encoding);
  }
}

if (!function_exists('mb_substr')) {
  function mb_substr($str, $start, $length = null, $encoding = 'UTF-8') {
    if (ICONV_ENABLED !== true)
      return isset($length) ? substr($str, $start, $length) : substr($str, $start);

    $length || $length = iconv_strlen($str, $encoding);
    return iconv_substr($str, $start, $length, $encoding);
  }
}
