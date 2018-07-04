<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Log {
  const EXT         = '.log';
  const DATE_FORMAT = 'H:i:s';
  const PERMISSIONS = 0777;

  private static $type = null;
  private static $fopens = [];

  public static function msg($text, $prefix = 'log-') {
    if (!is_dir(PATH_LOG)|| !isReallyWritable(PATH_LOG))
      return false;

    $newfile = !file_exists($path = PATH_LOG . $prefix . date('Y-m-d') . Log::EXT);

    if (!isset(self::$fopens[$path]))
      if (!$fopen = @fopen($path, 'ab'))
        return false;
      else
        self::$fopens[$path] = $fopen;

    for($written = 0, $length = charsetStrlen($text); $written < $length; $written += $result)
      if (($result = fwrite(self::$fopens[$path], charsetSubstr($text, $written))) === false)
        break;

    $newfile && @chmod($path, Log::PERMISSIONS);

    return is_int($result);
  }

  private static function logFormat($args) {
    $args = implode("\n" . cliColor('', 'N'), array_map(function($arg) { return cliColor('➜ ', 'G') . dump($arg); }, $args));
    return cliColor('※ ', 'R') . date(Log::DATE_FORMAT) . "\n" . cliColor(str_repeat('─', 40), 'N') . "\n" . $args . "\n\n\n";
  }

  public static function info($msg) {
    return self::msg(self::logFormat(func_get_args()), 'log-info-');
  }

  public static function error($msg) {
    return self::msg(self::logFormat(func_get_args()), 'log-error-');
  }

  public static function warning($msg) {
    return self::msg(self::logFormat(func_get_args()), 'log-warning-');
  }

  public static function model($msg) {
    return self::msg(self::logFormat(func_get_args()), 'log-model-');
  }

  public static function uploader($msg) {
    return self::msg(self::logFormat(func_get_args()), 'log-uploader-');
  }

  public static function closeAll() {
    foreach(self::$fopens as $fopen)
      fclose($fopen);
    return true;
  }

  // public static function queryLine() {
  //   self::$type || self::$type = ENVIRONMENT !== 'cmd' ? request_is_cli() ? cliColor('cli', 'c') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'C') : cliColor('web', 'p') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'P') : cliColor('cmd', 'y') . cliColor(' ➜ ', 'N') . cliColor(CMD_FILE, 'Y');
  //   @self::msg("\n" . self::$type . cliColor(' ╞' . str_repeat('═', CLI_LEN -(strlen(self::$type) - 31)) . "\n", 'N'), 'query-');
  //   return true;
  // }
  

  private static function queryFormat($args) {
    $valid = $args[0];
    $time = $args[1];
    $sql = $args[2];
    $vals = $args[3];
    $new = '';
    if (!self::$type) {
      $new = "\n" . cliColor(str_repeat('─', 80), 'N') . "\n";
      self::$type = ENVIRONMENT !== 'cmd' ? isCli() ? cliColor('cli', 'c') . cliColor(' ➜ ', 'N') . cliColor(implode('/', Url::segments()), 'C') : cliColor('web', 'p') . cliColor(' ➜ ', 'N') . cliColor(implode('/', Url::segments()), 'P') : cliColor('cmd', 'y') . cliColor(' ➜ ', 'N') . cliColor(CMD_FILE, 'Y');
    }
    return $new . self::$type . cliColor('│', 'N') . cliColor(date(Log::DATE_FORMAT), 'w') . cliColor(' ➜ ', 'N') . cliColor($time, $time < 999 ? $time < 99 ? $time < 9 ? 'w' : 'W' : 'Y' : 'R') . '' . cliColor('ms', $time < 999 ? $time < 99 ? $time < 9 ? 'N' : 'w' : 'y' : 'r') . cliColor('│', 'N') . ($valid ? cliColor('OK', 'g') : cliColor('GG', 'r')) . cliColor(' ➜ ', 'N') . call_user_func_array('sprintf', array_merge(array(preg_replace_callback('/\?/', function($matches) { return cliColor('%s', 'W'); }, $sql)), $vals)) . "\n";
  }
  public static function query($valid, $time, $sql, $vals) {
    @self::msg(self::queryFormat(func_get_args()), 'log-query-');
    return true;
  }

}
