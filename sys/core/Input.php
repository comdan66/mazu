<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Input {
  private static $ip = null;
  private static $headers = null;
  private static $inputStream = null;
  private static $hasSanitizeGlobals = null;

  private static function parsePut($putData) {
    $rawData = '';

    while ($chunk = fread($putData, 1024))
      $rawData .= $chunk;
    fclose($putData);

    $boundary = substr($rawData, 0, strpos($rawData, "\r\n"));

    if (empty($boundary)) {
      parse_str($rawData, $data);
      return $data;
    }

    $data = [];
    $parts = array_slice(explode($boundary, $rawData), 1);

    foreach ($parts as $part) {
      if ($part == "--\r\n" || $part == "--")
        break;

      $part = ltrim($part, "\r\n");
      list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

      $headers = [];
      $raw_headers = explode("\r\n", $raw_headers);

      foreach ($raw_headers as $header) {
        list($name, $value) = explode(':', $header);
        $headers[strtolower($name)] = ltrim($value, ' ');
      }

      if (isset($headers['content-disposition'])) {
        $filename = $tmpName = null;

        preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
        list(, $type, $name) = $matches;

        if (isset($matches[4])) {
          if (isset($_FILES[$matches[2]]))
            continue;

          $filename = $matches[4];
          $filenameParts = pathinfo($filename);
          $tmpName = tempnam(ini_get('upload_tmp_dir'), $filenameParts['filename']);

          preg_match_all('/^(?P<v1>\w+)(\s?\[(?P<v2>.?)\]\s?)$/', $matches[2], $tmp);
          $v1 = $tmp['v1'] ? $tmp['v1'][0] : null;
          $v2 = $tmp['v2'] ? $tmp['v2'][0] : null;

          if ($v1 !== null) {
            isset($_FILES[$v1]) || $_FILES[$v1] = ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []];

            if ($v2 !== null && $v2 !== '') {
              $_FILES[$v1]['name'][$v2] = $filename;
              $_FILES[$v1]['type'][$v2] = $value;
              $_FILES[$v1]['tmp_name'][$v2] = $tmpName;
              $_FILES[$v1]['error'][$v2] = 0;
              $_FILES[$v1]['size'][$v2] = strlen($body);
            } else {
              array_push($_FILES[$v1]['name'], $filename);
              array_push($_FILES[$v1]['type'], $value);
              array_push($_FILES[$v1]['tmp_name'], $tmpName);
              array_push($_FILES[$v1]['error'], 0);
              array_push($_FILES[$v1]['size'], strlen($body));
            }
          } else {
            $_FILES[$matches[2]] = [
              'name' => $filename,
              'type' => $value,
              'tmp_name' => $tmpName,
              'error' => 0,
              'size' => strlen ($body),
            ];
          }

          file_put_contents($tmpName, $body);
        } else {
          $data[$name] = substr($body, 0, strlen($body) - 2);
        }
      }
    }

    return $data;
  }

  private static function sanitizeGlobals() {
    if (self::$hasSanitizeGlobals)
      return;

    foreach ($_GET as $key => $val)
      $_GET[self::cleanInputKeys($key)] = self::cleanInputData($val);

    if (is_array($_POST))
      foreach ($_POST as $key => $val)
        $_POST[self::cleanInputKeys($key)] = self::cleanInputData($val);

    if (is_array($_COOKIE)) {
      unset($_COOKIE['$Version'], $_COOKIE['$Path'], $_COOKIE['$Domain']);

      foreach ($_COOKIE as $key => $val)
        if (($cookieKey = self::cleanInputKeys($key)) !== false) $_COOKIE[$cookieKey] = self::cleanInputData($val);
        else unset($_COOKIE[$key]);
    }

    $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

    self::$hasSanitizeGlobals = true;
  }

  private static function cleanInputKeys($str, $fatal = true) {
    if (!preg_match('/^[a-z0-9:_\/|-]+$/i', $str))
      return $fatal === true ? false : new GG('有不合法的字元！', 503);
      
    return UTF8_ENABLED === true ? cleanStr($str) : $str;
  }

  private static function cleanInputData($str) {
    if (is_array ($str)) {
      $t = [];
      foreach (array_keys($str) as $key)
        $t[self::cleanInputKeys($key)] = self::cleanInputData($str[$key]);
      return $t;
    }

    UTF8_ENABLED !== true || $str = cleanStr($str);
    $str = Security::removeInvisibleCharacters($str, false);

    return preg_replace('/(?:\r\n|[\r\n])/', PHP_EOL, $str);
  }

  private static function fetchFromArray(&$array, $index = null, $xssClean = null) {
    self::sanitizeGlobals();

    $index = $index === null ? array_keys($array) : $index;

    if (is_array($index)) {
      $output = [];
      foreach ($index as $key)
        $output[$key] = self::fetchFromArray($array, $key, $xssClean);
      return $output;
    }

    if (isset ($array[$index])) {
      $value = $array[$index];
    } else if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) {
      $value = $array;

      for ($i = 0; $i < $count; $i++) {
        $key = trim($matches[0][$i], '[]');
        if ($key === '')
          break;

        if (isset ($value[$key]))
          $value = $value[$key];
        else
          return null;
      }
    } else {
      return null;
    }

    $xssClean !== null || $xssClean = config('other', 'globalXssFiltering');
    return $xssClean ? Security::xssClean($value) : $value;
  }

  public static function get($index = null, $xssClean = true) {
    return self::fetchFromArray($_GET, $index, $xssClean);
  }

  public static function post($index = null, $xssClean = null) {
    return self::fetchFromArray($_POST, $index, $xssClean);
  }

  public static function cookie($index = null, $xssClean = null) {
    return self::fetchFromArray($_COOKIE, $index, $xssClean);
  }

  public static function server($index, $xssClean = null) {
    return self::fetchFromArray($_SERVER, $index, $xssClean);
  }

  public static function userAgent($xssClean = null) {
    return self::fetchFromArray($_SERVER, 'HTTP_USER_AGENT', $xssClean);
  }

  public static function requestHeaders($xssClean = true) {
    if (self::$headers !== null)
      return self::fetchFromArray(self::$headers, null, $xssClean);

    if (function_exists('apache_request_headers')) {
      self::$headers = apache_request_headers();
    } else {
      isset($_SERVER['CONTENT_TYPE']) && self::$headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];

      foreach ($_SERVER as $key => $val)
        if (sscanf($key, 'HTTP_%s', $header) === 1)
          self::$headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($header))))] = $_SERVER[$key];
    }

    return self::fetchFromArray(self::$headers, null, $xssClean);
  }

  public static function requestHeader($index = null, $xssClean = true) {
    $headers = self::requestHeaders($xssClean);

    if (!$index)
      return $headers;

    $headers = array_change_key_case($headers, CASE_LOWER);
    $index = strtolower($index);

    return isset($headers[$index]) ? $headers[$index] : null;
  }

  public static function ip() {
    if (self::$ip !== null)
      return self::$ip;

    self::$ip = self::server('REMOTE_ADDR');
    self::validIp(self::$ip) || self::$ip = '0.0.0.0';
    return self::$ip;
  }

  public static function validIp($ip, $which = '') {
    switch (strtolower($which)) {
      case 'ipv4':
        $which = FILTER_FLAG_IPV4;
        break;

      case 'ipv6':
        $which = FILTER_FLAG_IPV6;
        break;

      default:
        $which = null;
        break;
    }

    return (bool)filter_var($ip, FILTER_VALIDATE_IP, $which);
  }

  public static function putRawText() {
    return file_get_contents('php://input');
  }

  public static function putRawJson() {
    $put = file_get_contents('php://input');
    return isJson($put) ? $put : null;
  }

  public static function putFormData($index = null, $xssClean = true) {
    $puts = self::parsePut(fopen('php://input', 'r'));

    if (!$puts)
      return null;

    $xssClean !== null || $xssClean = config('other', 'globalXssFiltering');

    $puts = $xssClean ? array_map(function($put) { return Security::xssClean($put); }, $puts) : $puts;

    if ($index === null)
      return $puts;

    return isset($puts[$index]) ? $puts[$index] : null;
  }

  public static function inputStream($index = null, $xssClean = null) {
    if (self::$inputStream !== null)
      return self::fetchFromArray(self::$inputStream, $index, $xssClean);

    $rawInputStream = file_get_contents('php://input');

    parse_str($rawInputStream, self::$inputStream);
    is_array(self::$inputStream) || self::$inputStream = [];

    return self::fetchFromArray(self::$inputStream, $index, $xssClean);
  }

  public static function setCookie($name, $value, $expire = 0, $domain = null, $path = null, $prefix = null, $secure = null, $httponly = null) {
    if (is_array($name))
      foreach (['value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name'] as $item)
        if (isset($name[$item]))
          $$item = $name[$item];

    $prefix   !== null || $prefix   = config('cookie', 'prefix');
    $domain   !== null || $domain   = config('cookie', 'domain');
    $secure   !== null || $secure   = config('cookie', 'secure');
    $path     !== null || $path     = config('cookie', 'path');
    $httponly !== null || $httponly = config('cookie', 'httponly');
    $expire = time() + $expire;

    setcookie($prefix . $name, $value, $expire, $path, $domain, $secure, $httponly);
  }

  public static function transposedFilesArray($files) {
    $news = [];
    $filterSize = true;
    $keys = array_keys($files);

    for ($i = $j = 0, $c = count($files['name']), $keys = array_keys($files); $i < $c; $i++)
      if ((!is_array($files['size']) && (!$filterSize || $files['size'] != 0)) || (!$filterSize || $files['size'][$i] != 0)) {
        foreach ($keys as $key)
          $news[$j][$key] = is_array ($files[$key]) ? $files[$key][$i] : $files[$key];
        $j++;
      }

    return $news;
  }

  public static function transposedAllFilesArray($filesList) {
    $news = [];
    if ($filesList)
      foreach ($filesList as $key => $files)
        if (!is_array($files['name']))
          $files['size'] == 0 || $news[$key] = $files;
        else
          $news[$key] = self::transposedFilesArray($files);
    return $news;
  }

  public static function element($item, $array, $default = false) {
    return !isset($array[$item]) || $array[$item] == '' ? $default : $array[$item];
  }

  public static function getUploadFile($tagName, $type = 'all') {
    $list = self::element($tagName, self::transposedAllFilesArray($_FILES), []);

    if ($type == 'one')
      return count($list) ? $list[0] : null;
    else if (count ($list))
      return $list;
    else
      return [];
  }

  public static function file($index = null) {
    $isPut = (isset($_POST['_method']) && strtolower($_POST['_method']) == 'put') || (isset ($_SERVER['REQUEST_METHOD']) && strtolower ($_SERVER['REQUEST_METHOD']) == 'put');
    $isPut && self::parsePut(fopen('php://input', 'r'));

    if (!$_FILES)
      return [];

    if ($index === null)
      return self::transposedAllFilesArray($_FILES);

    preg_match_all('/^(?P<var>\w+)(\s?\[\s?\]\s?)$/', $index, $matches);
    $matches = $matches['var'] ? $matches['var'][0] : null;

    if (!$matches)
      return self::getUploadFile($index, 'one');

    return self::getUploadFile($matches);
  }
}
