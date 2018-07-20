<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Load {
  private static $cache = [];

  public static function file($path) {
    if (isset(self::$cache[$path]))
      return true;

    if (!(is_file($path) && is_readable($path)))
      return false;

    include_once $path;

    return self::$cache[$path] = true;
  }

  public static function path($path) {
    return self::file(PATH . $path);
  }

  public static function sysCore($path) {
    return self::file(PATH_SYS_CORE . $path);
  }

  public static function sysModel($path) {
    return self::file(PATH_SYS_MODEL . $path);
  }

  public static function sysLib($path) {
    return self::file(PATH_SYS_LIB . $path);
  }

  public static function sysFunc($path) {
    return self::file(PATH_SYS_FUNC . $path);
  }

  public static function app($path) {
    return self::file(PATH_APP . $path);
  }

  public static function core($path) {
    return self::file(PATH_CORE . $path);
  }

  public static function controller($path) {
    return self::file(PATH_CONTROLLER . $path);
  }

  public static function router($path) {
    return self::file(PATH_ROUTER . $path);
  }
}

class GG {
  public static $isApi = false;

  public function __construct($text, $code = 500, $contents = []) {
    
    responseStatusHeader($code);
    isCli() ? @system('clear') : @ob_end_clean();

    $type = !isCli() ? !class_exists('View') || GG::$isApi ? 'api' : 'html' : (class_exists('View') ? 'cli' : 'api');
    
    isset($contents['traces']) || $contents['traces'] = array_map(function($trace) { return ['path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''), 'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')]; }, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));
    
    if ($code === 404)
      $contents = [];

    if (isset($contents['msgs'][0]) && $contents['msgs'][0] instanceof Exception) {
      $contents['traces']  = array_map(function($trace) { return ['path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''), 'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')]; }, $contents['msgs'][0]->getTrace());
      $contents['msgs'] = array_map('dump', $contents['msgs']);
    }

    switch ($type) {
      default:
      case 'api':
        $contents = array_merge(['text' => dump($text)], $contents);

        @header('Content-Type: application/json');
        echo json_encode($contents);
        exit;
      
      case 'cli':
        echo View::maybe('error' . DIRECTORY_SEPARATOR . ($contents ? 'ggCli.php' : '404Cli.php'))->with('text', dump($text, 1))->with('contents', $contents)->get();
        exit;

      case 'html':
        echo View::maybe('error' . DIRECTORY_SEPARATOR . ($contents ? 'ggHtml.php' : '404Html.php'))->with('text', dump($text))->with('contents', $contents)->get();
        exit;
    }
  }
}

if (!function_exists('config')) {
  function config() {
    static $files, $keys;

    if (!$args = func_get_args())
      gg('Config 使用方式錯誤！');

    $fileName = array_shift($args);
    $argsStr  = implode('', $args);

    if (isset($keys[$fileName . $argsStr]))
      return $keys[$fileName . $argsStr];
    
    if (!isset($files[$fileName])) {
      if (!file_exists($path = PATH_APP . 'config' . DIRECTORY_SEPARATOR . ENVIRONMENT . DIRECTORY_SEPARATOR . $fileName . '.php') && !file_exists($path = PATH_APP . 'config' . DIRECTORY_SEPARATOR . $fileName . '.php'))
        gg('檔案名稱為「' . $fileName . '」的 Config 檔案不存在！');

      $files[$fileName] = include_once($path);
    }

    $tmp = $files[$fileName];

    foreach ($args as $arg)
      if (($tmp = isset($tmp[$arg]) ? $tmp[$arg] : null) === null)
        break;

    return $keys[$fileName . $argsStr] = $tmp;
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
    if (isCli())
      return ;
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
  function gg() {
    $args = func_get_args();
    new GG(array_shift($args), 500, ['msgs' => array_filter($args, function($arg) { return $arg !== '' && $arg !== null; })]);
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

if (!function_exists('cc')) {
  function cc($str, $fontColor = null, $backgroundColor = null, $options = []) {
    if ($str === "")
      return "";

    $colors = ['n' => '30', 'r' => '31', 'g' => '32', 'y' => '33', 'b' => '34', 'p' => '35', 'c' => '36', 'w' => '37'];
    $styles = ['underline' => '4', 'blink' => '5', 'reverse' => '7', 'hidden' => '8',
               'u' => '4',         'b' => '5',     'r' => '7',       'h' => '8'];

    $tmps = [];

    is_array($options) || $options = array_filter(array_map('trim', explode(',', $options)));

    if ($options = array_map('strtolower', $options))
      foreach ($options as $style)
        isset($styles[$style]) && array_push($tmps, ["\033[" . $styles[$style] . "m", "\033[0m"]);

    if ($backgroundColor !== null) {
      $c = $backgroundColor[0];
      $c = strtolower($c);
      isset($colors[$c]) && array_push($tmps, ["\033[" . ($colors[$c] + 10) . "m", "\033[0m"]);
    }

    if ($fontColor !== null) {
      strlen($fontColor) > 1 || $fontColor .= '_';
      list($c, $w) = str_split($fontColor);

      $w = $w === '_' ? ctype_upper($c) ? '2' : $w : $w;
      $c = strtolower($c);

      in_array($w, ['0', '1', '2']) || $w = '1';
      $w = $w !== '0' ? $w === '1' ? '0' : '1' : '2';

      isset($colors[$c]) && array_push($tmps, ["\033[" . $w . ';' . $colors[$c] . "m", "\033[0m"]);
    }

    foreach ($tmps as $tmp)
      $str = $tmp[0] . $str . $tmp[1];

    return $str;
  }
}

if (!function_exists('arrayFlatten')) {
  function arrayFlatten($arr) {
    $new = [];

    foreach ($arr as $key => $value)
      if (is_array($value))
        $new = array_merge($new, $value);
      else
        array_push($new, $value);

    return $new;
  }
}

if (!function_exists('errorHandler')) {
  function errorHandler($severity, $message, $filepath, $line) {
    $levels = [E_ERROR => 'Error', E_WARNING => 'Warning', E_PARSE => 'Parsing Error', E_NOTICE => 'Notice', E_CORE_ERROR => 'Core Error', E_CORE_WARNING => 'Core Warning', E_COMPILE_ERROR => 'Compile Error', E_COMPILE_WARNING => 'Compile Warning', E_USER_ERROR => 'User Error', E_USER_WARNING => 'User Warning', E_USER_NOTICE => 'User Notice', E_STRICT => 'Runtime Notice'];
    $isError = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

    if (($severity & error_reporting()) !== $severity)
      return;

    $details = [['title' => '類型', 'content' => isset($levels[$severity]) ? $levels[$severity] : $severity], ['title' => '訊息', 'content' => $message], ['title' => '位置', 'content' => $filepath . '(' . $line . ')']];
    $traces = array_map(function($trace) { return ['path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''), 'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')]; }, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT));

    str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')) && new GG($message, 500, ['details' => $details, 'traces' => $traces]);

    if (!$isError)
      return log::warning(['text' => $message, 'details' => $details, 'traces' => $traces]);

    responseStatusHeader(500);
    log::error(['text' => $message, 'details' => $details, 'traces' => $traces]);
    exit(1);
  }
}

if (!function_exists('exceptionHandler')) {
  function exceptionHandler($exception) {
    $message = '有 Exception 未使用 try catch！';
    $details = [['title' => '類型', 'content' => get_class($exception)], ['title' => '訊息', 'content' => $exception->getMessage()], ['title' => '位置', 'content' => $exception->getFile() . '(' . $exception->getLine() . ')']];
    $traces = array_map(function($trace) { return ['path' => (isset($trace['file']) ? str_replace('', '', $trace['file']) : '[呼叫函式]') . (isset($trace['line']) ? '(' . $trace['line'] . ')' : ''), 'info' => (isset($trace['class']) ? $trace['class'] : '') . (isset($trace['type']) ? $trace['type'] : '') . (isset($trace['function']) ? $trace['function'] : '') . (isset($trace['args']) ? '(' . implodeRecursive(', ', $trace['args']) . ')' : '')]; }, $exception->getTrace());

    str_ireplace(['off', 'none', 'no', 'false', 'null'], '', ini_get('display_errors')) && new GG($message, 500, ['details' => $details, 'traces' => $traces]);

    responseStatusHeader(500);
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

if (!function_exists('getNamespaces')) {
  function getNamespaces($className) {
    return array_slice(explode('\\', $className), 0, -1);
  }
}

if (!function_exists('deNamespace')) {
  function deNamespace($className) {
    $className = array_slice(explode('\\', $className), -1);
    return array_shift($className);
  }
}

if (!function_exists('dump')) {
  function dump($val, $l = 0) {
    if ($val === null) return str_repeat(' ', $l) . 'null';
    if (is_bool($val)) return str_repeat(' ', $l) . '' . ($val === true ? 'true' : 'false') . '';
    if (is_string($val)) return str_repeat(' ', $l) . '"' . $val . '"';
    if (is_numeric($val)) return str_repeat(' ', $l) . $val;
    if (is_array($val)) return str_repeat(' ', $l) . "[\n" . str_repeat(' ', $l + 2) . implode(",\n" . str_repeat(' ', $l + 2), array_map(function ($k, $v) use($l) { return dump($k) . ': ' . ltrim(dump($v, $l + 2));}, array_keys($val), $val)) . "\n" . str_repeat(' ', $l) . "]";
    if ($val instanceof \M\Model) return str_repeat(' ', $l) . 'Model(' . deNamespace(get_class($val)) . ") {\n" . str_repeat(' ', $l + 2) . implode(",\n" . str_repeat(' ', $l + 2), array_map(function ($k, $v) use($l) { return dump($k) . ': ' . ltrim(dump($v, $l + 2));}, array_keys($val->attrs()), $val->attrs())) . "\n" . str_repeat(' ', $l) . "}";
    if ($val instanceof \_M\DateTime) return str_repeat(' ', $l) . 'DateTime(' . '"' . $val . '"' . ")";
    if ($val instanceof \M\ImageUploader) return str_repeat(' ', $l) . "ImageUploader(" . '"' . $val . '"' . ") {\n" . str_repeat(' ', $l + 2) . '"versions": ' . "[" . implode(', ', array_map('dump', array_keys($val->versions()))) . "]" . "\n" . str_repeat(' ', $l) . "}";
    if ($val instanceof \M\FileUploader) return str_repeat(' ', $l) . "FileUploader(" . '"' . $val . '"' . ")";
    if ($val instanceof Exception) return $val->getMessage();
    if (is_object($val) && method_exists($val, '__toString')) return str_repeat(' ', $l) . '"' . $val . '"';
    if (is_object($val) && !method_exists($val, '__toString')) return str_repeat(' ', $l) . 'Object(' . get_class($val) . ')';

    gg('dump 函式錯誤！型態：' . gettype($val));
  }
}

if (!function_exists('umaskChmod')) {
  function umaskChmod($pathname, $mode = 0777) {
    $oldmask = umask(0);
    @chmod($pathname, $mode);
    umask($oldmask);
  }
}

if (!function_exists('umaskMkdir')) {
  function umaskMkdir($pathname, $mode = 0777, $recursive = false) {
    $oldmask = umask(0);
    $return = @mkdir($pathname, $mode, $recursive);
    umask($oldmask);
    return $return;
  }
}

if (!function_exists('isJson')) {
  function isJson(&$string, $array = false) {
   $string = json_decode($string, $array);
   return (json_last_error() === JSON_ERROR_NONE);
  }
}

if (!function_exists('transaction')) {
  function transaction($closure, &...$args) {
    function_exists('\M\transaction') || \M\useModel();
    
    is_callable($closure) || gg('transaction 第一個參數必須可呼叫！');

    if (call_user_func_array('\M\transaction', array_merge([$closure], $args)))
      return null;

    Router::setStatus(400);

    return '資料庫處理錯誤！';
  }
}

if (!function_exists('arrayColumn')) {
  function arrayColumn(array $objs, $key) {
    if (!$objs)
      return [];

    $objs[0] instanceof \M\Model && $objs = \M\modelsToArray($objs);
    return array_column($objs, $key);
  }
}

if (!function_exists('items')) {
  function items($values, $texts, $k1 = 'value', $k2 = 'text') {
    return count($values) == count($texts) ? array_map(function($value, $text) use($k1, $k2) { return [$k1 => '' . $value, $k2 => '' . $text]; }, $values, $texts) : [];
  }
}

if (!function_exists('isUploadFileFormat')) {
  function isUploadFileFormat($file) {
    return isset($file['name'], $file['type'], $file['tmp_name'], $file['error'], $file['size']);
  }
}

if (!function_exists('uploadFileInFormats')) {
  function uploadFileInFormats($file, $formats) {
    static $extension;
    
    if (!isUploadFileFormat($file))
      return false;

    $formats = array_unique(array_map('trim', $formats));

    if (!$format = pathinfo($file['name'], PATHINFO_EXTENSION)) {
      $extension || $extension = config('extension');
      foreach ($extension as $ext => $mime)
        if (in_array($file['type'], $mime) && ($format = $ext))
          break;
    }

    return $format && in_array($format, $formats);
  }
}

// if (!function_exists('run')) {
//   function run(array $funcs = []) {
//     foreach ($funcs as $closure => &$args) {
//       if (!(is_array($args) && $args && is_callable($closure)))
//         return '函式錯誤！';

//       $str = '$str = $closure(' . implode(', ', array_map(function($i, $arg) { return '$args[' . $i . ']'; }, array_keys($args), $args)) . ');';
//       eval($str);

//       if ($str)
//         return $str;
//     }

//     return null;
//   }
// }



/* ------------------------------------------------------
 *  定義自己的 Error Handler
 * ------------------------------------------------------ */
set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');
register_shutdown_function('shutdownHandler');

isPhpVersion('5.6') || gg('PHP 版本太舊，請大於等於 5.6 版本！');
