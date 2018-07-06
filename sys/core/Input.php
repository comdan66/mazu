<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Input {
  private static $hassanitizeglobals = null;
  private static $headers = null;
  private static $ip = null;
  private static $inputstream = null;

  const put_form_data = '_p_f_d';
  const put_raw_text = '_p_r_t';
  const put_raw_json = '_p_r_j';
  const put_x_www_urlencoded = '_p_x_w_u';

  private static function sanitizeglobals() {
    if (self::$hassanitizeglobals)
      return;

    foreach ($_get as $key => $val)
      $_get[self::cleaninputkeys($key)] = self::cleaninputdata($val);

    if (is_array($_post))
      foreach ($_post as $key => $val)
        $_post[self::cleaninputkeys($key)] = self::cleaninputdata($val);

    if (is_array($_cookie)) {
      unset($_cookie['$version'], $_cookie['$path'], $_cookie['$domain']);

      foreach ($_cookie as $key => $val)
        if (($cookiekey = self::cleaninputkeys($key)) !== false)
          $_cookie[$cookiekey] = self::cleaninputdata($val);
        else
          unset($_cookie[$key]);
    }

    $_server['php_self'] = strip_tags($_server['php_self']);

    self::$hassanitizeglobals = true;
  }
  
  private static function cleaninputkeys($str, $fatal = true) {
    if (!preg_match('/^[a-z0-9:_\/|-]+$/i', $str))
      if ($fatal === true)
        return false;
      else
        gg('有不合法的字元！', 503);

    if (utf8_enabled === true)
      return cleanstr($str);

    return $str;
  }
  
  private static function cleaninputdata($str) {
    if (is_array($str)) {
      $t = [];
      foreach (array_keys($str) as $key)
        $t[self::cleaninputkeys($key)] = self::cleaninputdata($str[$key]);
      return $t;
    }

    utf8_enabled === true && $str = cleanstr($str);

    $str = security::removeinvisiblecharacters($str, false);

    return preg_replace('/(?:\r\n|[\r\n])/', php_eol, $str);
  }

  private static function fetchfromarray(&$array, $index = null, $xssclean = null) {
    self::sanitizeglobals();

    $index = $index === null ? array_keys($array) : $index;

    if (is_array($index)) {
      $output = [];
      foreach ($index as $key)
        $output[$key] = self::fetchfromarray($array, $key, $xssclean);
      return $output;
    }

    if (isset($array[$index])) {
      $value = $array[$index];
    } else if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) {
      $value = $array;
      for ($i = 0; $i < $count; $i++) {
        $key = trim($matches[0][$i], '[]');
        
        if ($key === '')
          break;

        if (isset($value[$key]))
          $value = $value[$key];
        else
          return null;
      }
    } else {
      return null;
    }

    $xssclean !== null || $xssclean = config('other', 'globalxssfiltering');
    return $xssclean ? security::xssclean($value) : $value;
  }
  
  public static function get($index = null, $xssclean = true) {
    return self::fetchfromarray($_get, $index, $xssclean);
  }
  
  public static function post($index = null, $xssclean = null) {
    return self::fetchfromarray($_post, $index, $xssclean);
  }

  public static function cookie($index = null, $xssclean = null) {
    return self::fetchfromarray($_cookie, $index, $xssclean);
  }
  
  public static function server($index, $xssclean = null) {
    return self::fetchfromarray($_server, $index, $xssclean);
  }
  
  public static function useragent($xssclean = null) {
    return self::fetchfromarray($_server, 'http_user_agent', $xssclean);
  }
  
  public static function requestheaders($xssclean = true) {
    if (self::$headers !== null)
      return self::fetchfromarray(self::$headers, null, $xssclean);

    if (function_exists('apache_request_headers')) {
      self::$headers = apache_request_headers();
    } else {
      if (isset($_server['content_type']))
        self::$headers['content-type'] = $_server['content_type'];

      foreach ($_server as $key => $val)
        if (sscanf($key, 'http_%s', $header) === 1) {
          $header = str_replace('_', ' ', strtolower($header));
          $header = str_replace(' ', '-', ucwords($header));

          self::$headers[$header] = $_server[$key];
        }
    }

    return self::fetchfromarray(self::$headers, null, $xssclean);
  }
  
  public static function requestheader($index = null, $xssclean = true) {
    $headers = self::requestheaders($xssclean);
    if (!$index)
      return $headers;

    $headers = array_change_key_case($headers, case_lower);
    $index = strtolower($index);

    if (!isset($headers[$index]))
      return null;

    $xssclean !== null || $xssclean = config('other', 'globalxssfiltering');
    

    return $xssclean ? security::xssclean ($headers[$index]) : $headers[$index];
  }
  
  public static function ip() {
    if (self::$ip !== null) return self::$ip;

    $proxy_ips = config ('other', 'proxy_ips');

    if ($proxy_ips && is_string ($proxy_ips))
      $proxy_ips = explode (',', str_replace (' ', '', $proxy_ips));

    self::$ip = self::server ('remote_addr');

    if ($proxy_ips && is_array ($proxy_ips)) {
      foreach (array ('http_x_forwarded_for', 'http_client_ip', 'http_x_client_ip', 'http_x_cluster_client_ip') as $header)
        if (($spoof = self::server ($header)) !== null) {
          sscanf ($spoof, '%[^,]', $spoof);
          if (!self::validip ($spoof)) $spoof = null;
          else break;
        }

      if ($spoof) {
        for ($i = 0, $c = count($proxy_ips); $i < $c; $i++) {
          if (strpos($proxy_ips[$i], '/') === false) {
            if ($proxy_ips[$i] === self::$ip) {
              self::$ip = $spoof;
              break;
            }
            continue;
          }

          isset ($separator) || $separator = self::validip (self::$ip, 'ipv6') ? ':' : '.';

          if (strpos ($proxy_ips[$i], $separator) === false)
            continue;

          if (!isset ($ip, $sprintf)) {
            if ($separator === ':') {
              $ip = explode (':', str_replace ('::', str_repeat (':', 9 - substr_count (self::$ip, ':')), self::$ip));

              for ($j = 0; $j < 8; $j++)
                $ip[$j] = intval ($ip[$j], 16);

              $sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
            } else {
              $ip = explode ('.', self::$ip);
              $sprintf = '%08b%08b%08b%08b';
            }

            $ip = vsprintf ($sprintf, $ip);
          }

          sscanf ($proxy_ips[$i], '%[^/]/%d', $netaddr, $masklen);

          if ($separator === ':') {
            $netaddr = explode (':', str_replace ('::', str_repeat (':', 9 - substr_count ($netaddr, ':')), $netaddr));

            for ($j = 0; $j < 8; $j++)
              $netaddr[$j] = intval ($netaddr[$j], 16);
          } else {
            $netaddr = explode ('.', $netaddr);
          }

          if (strncmp ($ip, vsprintf ($sprintf, $netaddr), $masklen) === 0) {
            self::$ip = $spoof;
            break;
          }
        }
      }
    }

    if (!self::validip (self::$ip))
      return self::$ip = '0.0.0.0';

    return self::$ip;
  }
  
  public static function validip($ip, $which = '') {
    switch (strtolower ($which)) {
      case 'ipv4':
        $which = filter_flag_ipv4;
        break;

      case 'ipv6':
        $which = filter_flag_ipv6;
        break;

      default:
        $which = null;
        break;
    }

    return (bool)filter_var ($ip, filter_validate_ip, $which);
  }

  public static function put($index = null, $form = 'x-www-form-urlencoded', $xssclean = true) {
    if ($index === self::put_raw_text || $index === self::put_raw_json) {
      $form = $index;
      $index = null;
    }

    switch ($form) {
      case self::put_raw_text:
        return file_get_contents ('php://input');
        break;

      case self::put_raw_json:
        $put = file_get_contents ('php://input');
        return isjson ($put) ? $put : null;
        break;

      case self::put_form_data:
        $puts = parse_put ();
        break;

      default:
        parse_str (file_get_contents ('php://input'), $puts);
        break;
    }

    if (!$puts) return null;

    $puts = $xssclean ? array_map (function ($put) { return security::xssclean ($put);}, $puts) : $puts;

    if ($index === null)
      return $puts;

    return isset ($puts[$index]) ? $puts[$index] : null;
  }

  public static function putfile($index = null) {
    self::put ($index, self::put_form_data);
    return self::postfile ($index);
  }

  public static function inputstream($index = null, $xssclean = null) {
    if (self::$inputstream !== null)
      return self::fetchfromarray (self::$inputstream, $index, $xssclean);

    $raw_input_stream = file_get_contents ('php://input');
    parse_str ($raw_input_stream, self::$inputstream);
    is_array (self::$inputstream) || self::$inputstream = [];

    return self::fetchfromarray (self::$inputstream, $index, $xssclean);
  }

  public static function setcookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = null, $httponly = null) {
    if (is_array ($name))
      foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name') as $item)
        if (isset($name[$item]))
          $$item = $name[$item];

    if ($prefix === '' && config ('cookie', 'prefix') !== '')
      $prefix = config ('cookie', 'prefix');

    if ($domain == '' && config ('cookie', 'domain') != '')
      $domain = config ('cookie', 'domain');

    if ($path === '/' && config ('cookie', 'path') !== '/')
      $path = config ('cookie', 'path');

    $secure = ($secure === null && config ('cookie', 'secure') !== null) ? (bool) config ('cookie', 'secure') : (bool) $secure;

    $httponly = ($httponly === null && config ('cookie', 'httponly') !== null) ? (bool) config ('cookie', 'httponly') : (bool) $httponly;

    $expire = !is_numeric ($expire)? time() - 86500 : (($expire > 0) ? time() + $expire : 0);

    setcookie ($prefix . $name, $value, $expire, $path, $domain, $secure, $httponly);
  }

  public static function transposedfilesarray($files) {
    $filter_size = true;
    $new_array = [];
    $keys = array_keys ($files);
    if (is_array ($files['name'])) {
      foreach ($files['name'] as $i => $val)
        if ((!is_array ($files['size']) && (!$filter_size || $files['size'] != 0)) || (!$filter_size || $files['size'][$i] !=0))
          foreach ($keys as $key)
            $new_array[$i][$key] = is_array ($files[$key]) ? $files[$key][$i] : $files[$key];
    } else {
      for ($i = $j = 0, $c = count ($files['name']), $keys = array_keys ($files); $i < $c; $i++)
        if ((!is_array ($files['size']) && (!$filter_size || $files['size']!=0)) || (!$filter_size || $files['size'][$i] !=0)) {
          foreach ($keys as $key)
            $new_array[$j][$key] = is_array ($files[$key]) ? $files[$key][$i] : $files[$key];
          $j++;
        }
    }
    return $new_array;
  }

  public static function transposedallfilesarray($files_list) {
    $new_array = [];
    if ($files_list)
      foreach ($files_list as $key => $files)
        $new_array[$key] = self::transposedfilesarray ($files);

    return $new_array;
  }

  public static function element($item, $array, $default = false) {
    return !isset ($array[$item]) || ($array[$item] == "") ? $default : $array[$item];
  }

  public static function getuploadfile($tag_name, $type = 'all') {
    $list = self::element ($tag_name, self::transposedallfilesarray ($_files), []);
    if ($type == 'one') if (count ($list)) return $list[0]; else return null;
    else if (count ($list)) return $list; else return [];
  }

  public static function file($index = null) {
    if (!$_files)
      return [];

    if ($index === null)
      return array_filter (array_map (function ($t) {
        return is_array ($t) && count ($t) == 1 ? $t[0] : $t;
      }, self::transposedallfilesarray ($_files)));
      // return array_filter (self::transposedallfilesarray ($_files));

    // if (isset ($_files[$index]['name']) && count ($_files[$index]['name']) > 1)
    //   $index = $index . '[]';

    preg_match_all ('/^(?p<var>\w+)(\s?\[\s?\]\s?)$/', $index, $matches);
    return ($matches = $matches['var'] ? $matches['var'][0] : null) ? self::getuploadfile (isset ($_post['_method']) && strtolower ($_post['_method']) == 'put' || isset ($_server['request_method']) && strtolower ($_server['request_method']) == 'put' ? $matches . '[]' : $matches) : self::getuploadfile ($index, 'one');
  }
}


if (!function_exists ('parse_put')) {
  function parse_put () {
    $raw_data = '';
    $putdata = fopen ("php://input", "r");

    while ($chunk = fread ($putdata, 1024))
      $raw_data .= $chunk;
    fclose ($putdata);

    $boundary = substr ($raw_data, 0, strpos ($raw_data, "\r\n"));

    if (empty ($boundary)) {
      parse_str ($raw_data, $data);
      return $data;
    }

    $data = [];
    $parts = array_slice (explode ($boundary, $raw_data), 1);

    foreach ($parts as $part) {
      if ($part == "--\r\n" || $part == "--") break;

      $part = ltrim ($part, "\r\n");
      list ($raw_headers, $body) = explode ("\r\n\r\n", $part, 2);

      $headers = [];
      $raw_headers = explode ("\r\n", $raw_headers);
      foreach ($raw_headers as $header) {
        list ($name, $value) = explode (':', $header);
        $headers[strtolower ($name)] = ltrim ($value, ' ');
      }

      if (isset ($headers['content-disposition'])) {
        $filename = $tmp_name = null;

        preg_match ('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
        list (, $type, $name) = $matches;

        if (isset ($matches[4])) {
          if (isset ($_FILES[$matches[2]]))
            continue;

          $filename = $matches[4];

          $filename_parts = pathinfo ($filename);
          $tmp_name = tempnam (ini_get ('upload_tmp_dir'), $filename_parts['filename']);

          $_FILES[$matches[2]] = array ('error' => 0, 'name' => $filename, 'tmp_name' => $tmp_name, 'size' => strlen ($body), 'type' => $value);

          file_put_contents ($tmp_name, $body);
        } else {
          $data[$name] = substr ($body, 0, strlen ($body) - 2);
        }
      }
    }
    return $data;
  }
}

if (!function_exists ('isJson')) {
  function isJson (&$string, $array = false) {
   $string = json_decode ($string, $array);
   return (json_last_error() === JSON_ERROR_NONE);
  }
}
