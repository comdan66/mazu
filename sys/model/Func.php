<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

if (!function_exists('\M\getNamespaces')) {
  function getNamespaces($className) {
    return array_slice(explode('\\', $className), 0, -1);
  }
}

if (!function_exists('\M\deNamespace')) {
  function deNamespace($className) {
    $className = array_slice(explode('\\', $className), -1);
    return array_shift($className);
  }
}

if (!function_exists('\M\transaction')) {
  function transaction($closure, &...$args) {
    if (!is_callable($closure))
      return false;

    try {
      \_M\Connection::instance()->transaction();

      if (call_user_func_array($closure, $args))
        return \_M\Connection::instance()->commit();

      \_M\Connection::instance()->rollback();
      return false;
    } catch (\Exception $e) {
      \_M\Connection::instance()->rollback();
      \Log::model($e);
    }

    return true;
  }
}

if (!function_exists('\M\modelsColumn')) {
  function modelsColumn($arr, $key) {
    return array_map(function($t) use($key) {
      is_callable($key) && $key = $key();
      return $t->$key;
    }, $arr);
  }
}

if (!function_exists('\M\getRandomName')) {
  function getRandomName() {
    return md5(uniqid(mt_rand(), true));
  }
}

if (!function_exists('\M\webFileExists')) {
  function webFileExists($url, $cainfo = null) {
    $options = [CURLOPT_URL => $url, CURLOPT_NOBODY => 1, CURLOPT_FAILONERROR => 1, CURLOPT_RETURNTRANSFER => 1];

    is_readable($cainfo) && $options[CURLOPT_CAINFO] = $cainfo;

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    return curl_exec($ch) !== false;
  }
}

if (!function_exists('\M\downloadWebFile')) {
  function downloadWebFile($url, $fileName = null, $isUseReffer = false, $cainfo = null) {
    if (!webFileExists($url, $cainfo))
      return null;

    is_readable($cainfo) && $url = str_replace(' ', '%20', $url);

    $options = [CURLOPT_URL => $url, CURLOPT_TIMEOUT => 120, CURLOPT_HEADER => false, CURLOPT_MAXREDIRS => 10, CURLOPT_AUTOREFERER => true, CURLOPT_CONNECTTIMEOUT => 30, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.76 Safari/537.36"];

    is_readable ($cainfo) && $options[CURLOPT_CAINFO] = $cainfo;

    $isUseReffer && $options[CURLOPT_REFERER] = $url;

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $data = curl_exec($ch);
    curl_close($ch);

    if (!$fileName)
      return $data;

    $write = fopen($fileName, 'w');
    fwrite($write, $data);
    fclose($write);

    $oldmask = umask(0);
    @chmod($fileName, 0777);
    umask($oldmask);

    return filesize($fileName) ? $fileName : null;
  }
}

if (!function_exists('\M\reverseOrder')) {
  function reverseOrder($order) {
    return trim($order) ? implode(', ', array_map(function($part) {
      $v = trim(strtolower($part));
      return strpos($v,' asc') === false ? strpos($v,' desc') === false ? $v . ' DESC' : preg_replace('/desc/i', 'ASC', $v) : preg_replace('/asc/i', 'DESC', $v);
    }, explode(',', $order))) : 'order';
  }
}

if (!function_exists('\M\cast')) {
  function cast($type, $val, $checkFormat) {
    if ($val === null)
      return null;
    
    switch ($type) {
      case 'tinyint': case 'smallint': case 'mediumint': case 'int': case 'bigint':
        if (is_int($val))
          return $val;
        elseif (is_numeric($val) && floor($val) != $val)
          return (int)$val;
        elseif (is_string($val) && is_float($val + 0))
          return (string) $val;
        elseif (is_float($val) && $val >= PHP_INT_MAX)
          return number_format($val, 0, '', '');
        else
          return (int)$val;
      
      case 'float': case 'double': case 'numeric': case 'decimal': case 'dec':
        return (double)$val;

      case 'datetime': case 'timestamp': case 'date': case 'time':
        if (!$val)
          return null;

        $val = \_M\DateTime::createByString($val, $type);
        $checkFormat && !$val->isFormat() && \gg('cast 轉換失敗！', 'Type：' . $type, 'CheckFormat：' . $checkFormat);
        return $val;

      default:
        if ($val instanceof \M\Uploader)
          return $val;
        return (string)$val;
    }
    return $val;
  }
}

// public static function transactionError ($closure, &...$args) {
//   if (!is_callable ($closure))
//     return false;

//   $class = get_called_class ();
//   return  call_user_func_array (array ($class, 'transaction'), array_merge (array ($closure), $args)) ? null : '資料庫處理錯誤！';
// }
