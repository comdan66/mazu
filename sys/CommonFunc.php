<?php

if (!function_exists('load')) {
  function load($path, $error = null) {
    static $cache;
    
    if (isset($cache[$path]))
      return true;

    $error || $error = '載入檔案「' . $path . '」失敗！';

    if (!(is_file($path) && is_readable($path)))
      return false;

    require_once $path;

    return $cache[$path] = true;
  }
}

if (!function_exists('config')) {
  function config() {
    static $files, $keys;

    if (!$args = func_get_args())
      gg('Config 使用方式錯誤！');

    $fileName = array_shift($args);
    $argsStr  = implode('', $args);

    if (isset($keys[$argsStr]))
      return $keys[$argsStr];
    
    if (!isset($files[$fileName])) {
      if (!file_exists($path = PATH_APP . 'config' . DIRECTORY_SEPARATOR . ENVIRONMENT . DIRECTORY_SEPARATOR . $fileName . '.php') && !file_exists($path = PATH_APP . 'config' . DIRECTORY_SEPARATOR . $fileName . '.php'))
        gg('檔案名稱為「' . $fileName . '」的 Config 檔案不存在！');

      $files[$fileName] = include_once($path);
    }

    $tmp = $files[$fileName];

    foreach ($args as $arg)
      if (($tmp = isset($tmp[$arg]) ? $tmp[$arg] : null) === null)
        break;

    return $keys[$argsStr] = $tmp;
  }
}

if (!function_exists('isCli')) {
  function isCli() {
    return PHP_SAPI === 'cli' || defined('STDIN');
  }
}

if (!function_exists('isPhpVersion')) {
  function isPhpVersion($version) {
    static $versions;
    return !isset($versions[$version =(string)$version]) ? $versions[$version] = version_compare(PHP_VERSION, $version, '>=') : $versions[$version];
  }
}

// https://zh.wikipedia.org/wiki/HTTP%E7%8A%B6%E6%80%81%E7%A0%81
if (!function_exists('responseStatusText')) {
  function responseStatusText ($code) {
    $responseStatusText = [
      100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing',
      200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-Status', 208 => 'Already Reported', 226 => 'IM Used',
      300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 306 => 'Switch Proxy', 307 => 'Temporary Redirect', 308 => 'Permanent Redirect',
      400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 420 => 'Enhance Your Caim', 421 => 'Misdirected Request', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Unodered Cellection', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 444 => 'No Response', 450 => 'Blocked by Windows Parental Controls', 451 => 'Unavailable For Legal Reasons', 494 => 'Request Header Too Large',
      500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 510 => 'Not Extended', 511 => 'Network Authentication Required'
    ];

    return isset($responseStatusText[$code]) ? $responseStatusText[$code] : '';
  }
}

if (!function_exists('responseStatusHeader')) {
  function responseStatusHeader($code, $str = '') {
      $str = responseStatusText($code);
      $str || $str = responseStatusText($code = 500);

      if (strpos(PHP_SAPI, 'cgi') === 0)
        return header('Status: ' . $code . ' ' . $str, true);

      in_array(($protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'), ['HTTP/1.0', 'HTTP/1.1', 'HTTP/2']) || $protocol = 'HTTP/1.1';
      return header($protocol . ' ' . $code . ' ' . $str, true, $code);
  }
}


if (!function_exists('implodeRecursive')) {
  function implodeRecursive($glue, $pieces) {
    $ret = '';

    foreach ($pieces as $piec)
      $ret .= isset($piec) ? !is_object($piec) ? !is_bool($piec) ? is_array($piec) ? '[' . implodeRecursive($glue, $piec) . ']' . $glue : $piec . $glue : ($piec ? 'true' : 'false') . $glue : get_class($piec) . $glue : 'null' . $glue;

    $ret = substr($ret, 0, 0 - strlen($glue));

    return $ret;
  }
}

if (!function_exists('gg')) {
  function gg($text, $code = 500, $contents = []) {
    static $api;
    

    if ($text === null)
      return $api = $code;

    isCli() || responseStatusHeader(500);
    isCli() ? @system('clear') : @ob_end_clean();

    $statusText = ($statusText = responseStatusText($code)) ? $code . ' ' . $statusText : '';
    
    isset($contents['traces']) || $contents['traces'] = array_map(function($trace) {
      return [
        'path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''),
        'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')
      ];
    }, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));
    
    if (!class_exists('View') || $api === 'api') {
      $contents = array_merge(array ('text' => $text), $contents);
      @header('Content-Type: application/json');
      echo json_encode($contents);
    } else if (isCli())
      echo View::maybe('error' . DIRECTORY_SEPARATOR . 'ggCli.php')
               ->with('text', dump($text, 1))
               ->with('contents', $contents)
               ->get();
    else
      echo View::maybe('error' . DIRECTORY_SEPARATOR . 'ggHtml.php')
               ->with('text', dump($text))
               ->with('contents', $contents)
               ->get();
    exit;
  }
}

if (!function_exists('isReallyWritable')) {
  function isReallyWritable($file) {
    if (DIRECTORY_SEPARATOR === '/')
      return is_writable($file);

    if (is_dir($file)) {
      if (($fp = @fopen($file = rtrim($file, '/') . '/' . md5(mt_rand()), 'ab')) === false)
        return false;
 
      fclose($fp);
      @chmod($file, 0777);
      @unlink($file);

      return true;
    }

    if (!is_file($file) || ($fp = @fopen($file, 'ab')) === false)
      return false;
 
    fclose($fp);
    return true;
  }
}

if (!function_exists('cliColor')) {
  function cliColor($str, $fontColor = null, $backgroundColor = null) {
    if ($str == "")
      return "";

    $keys = ['n' => '30', 'w' => '37', 'b' => '34', 'g' => '32', 'c' => '36', 'r' => '31', 'p' => '35', 'y' => '33'];

    $newStr = "";

    if ($fontColor && in_array(strtolower($fontColor), array_map('strtolower', array_keys($keys)))) {
      $fontColor = !in_array(ord($fontColor[0]), array_map('ord', array_keys($keys))) ? in_array(ord($fontColor[0]) | 0x20, array_map('ord', array_keys($keys))) ? '1;' . $keys[strtolower($fontColor[0])] : null : $keys[$fontColor[0]];
      $newStr .= $fontColor ? "\033[" . $fontColor . "m" : "";
    }

    $newStr .= $backgroundColor && in_array(strtolower($backgroundColor), array_map('strtolower', array_keys($keys))) ? "\033[" . ($keys[strtolower($backgroundColor[0])] + 10) . "m" : "";

    if ($has_new_line = substr($str, -1) == "\n")
      $str = substr($str, 0, -1);

    $newStr .=  $str . "\033[0m";
    $newStr = $newStr . ($has_new_line ? "\n" : "");
    return $newStr;
  }
}

// if (!function_exists('arr2dTo1d')) {
//   function arr2dTo1d($arr) {
//     $new = [];

//     foreach ($arr as $key => $value)
//       if (is_array($value))
//         $new = array_merge($new, $value);
//       else
//         array_push($new, $value);

//     return $new;
//   }
// }

if (!function_exists('errorHandler')) {
  function errorHandler($severity, $message, $filepath, $line) {
    $levels = [E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parsing Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Runtime Notice'];
    $isError = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

    if (($severity & error_reporting()) !== $severity)
      return;

    $details = [['title' => '類型', 'content' => isset($levels[$severity]) ? $levels[$severity] : $severity], ['title' => '訊息', 'content' => $message], ['title' => '位置', 'content' => $filepath . '(' . $line . ')']];
    $traces = array_map(function($trace) { return ['path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''), 'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')]; }, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));

    str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')) && gg($message, 500, ['details' => $details, 'traces' => $traces]);

    if (!$isError)
      return log::warning(['text' => $message, 'details' => $details, 'traces' => $traces]);

    isCli() || responseStatusHeader(500);
    log::error(['text' => $message, 'details' => $details, 'traces' => $traces]);
    exit(1);
  }
}

if (!function_exists('exceptionHandler')) {
  function exceptionHandler($exception) {
    $message = '有 Exception 未使用 try catch！';
    $details = [['title' => '類型', 'content' => get_class($exception)], ['title' => '訊息', 'content' => $exception->getMessage()], ['title' => '位置', 'content' => $exception->getFile() . '(' . $exception->getLine() . ')']];
    $traces = array_map(function($trace) { return ['path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''), 'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')]; }, $exception->getTrace());

    str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')) && gg($message, 500, ['details' => $details, 'traces' => $traces]);

    isCli() || responseStatusHeader(500);
    log::error(['text' => $message, 'details' => $details, 'traces' => $traces]);
    exit(1);
  }
}

if (!function_exists('shutdownHandler')) {
  function shutdownHandler() {
    $lastError = error_get_last();
    isset($lastError['type']) && ($lastError['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)) && errorHandler($lastError['type'], $lastError['message'], $lastError['file'], $lastError['line']);
  }
}

if (!function_exists('dump')) {
  function dump($val, $l = 0) {
    if ($val === null) return str_repeat(' ', $l) . 'null';
    if (is_string($val)) return str_repeat(' ', $l) . '"' . $val . '"';
    if (is_numeric($val)) return str_repeat(' ', $l) . $val;
    if (is_array($val)) return str_repeat(' ', $l) . "[\n" . str_repeat(' ', $l + 2) . implode(",\n" . str_repeat(' ', $l + 2), array_map(function ($k, $v) use($l) { return dump($k) . ': ' . ltrim(dump($v, $l + 2));}, array_keys($val), $val)) . "\n" . str_repeat(' ', $l) . "]";
    if ($val instanceof \M\Model) return str_repeat(' ', $l) . 'Model(' . \M\deNamespace(get_class($val)) . ") {\n" . str_repeat(' ', $l + 2) . implode(",\n" . str_repeat(' ', $l + 2), array_map(function ($k, $v) use($l) { return dump($k) . ': ' . ltrim(dump($v, $l + 2));}, array_keys($val->attrs()), $val->attrs())) . "\n" . str_repeat(' ', $l) . "}";
    if ($val instanceof \_M\DateTime) return str_repeat(' ', $l) . 'DateTime(' . '"' . $val . '"' . ")";
    if ($val instanceof \M\ImageUploader) return str_repeat(' ', $l) . "ImageUploader(" . '"' . $val . '"' . ") {\n" . str_repeat(' ', $l + 2) . '"versions": ' . "[" . implode(', ', array_map('dump', array_keys($val->versions()))) . "]" . "\n" . str_repeat(' ', $l) . "}";
    if ($val instanceof \M\FileUploader) return str_repeat(' ', $l) . "FileUploader(" . '"' . $val . '"' . ")";
    if (is_object($val) && method_exists($val, '__toString')) return str_repeat(' ', $l) . '"' . $val . '"';
    if (is_object($val) && !method_exists($val, '__toString')) return str_repeat(' ', $l) . 'Object(' . get_class($val) . ')';
  }
}

/* ------------------------------------------------------
 *  定義自己的 Error Handler
 * ------------------------------------------------------ */
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');
register_shutdown_function('shutdownHandler');
