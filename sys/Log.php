<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Log {
  private static $extension = '.log';
  private static $permissions = 0777;
  private static $dateFormat = 'H:i:s';
  private static $fopens = [];

  public static function msg($text, $prefix = 'log-') {
    if (!is_dir(PATH_LOG)|| !isReallyWritable(PATH_LOG))
      return false;

    $newfile = !file_exists($path = PATH_LOG . $prefix . date('Y-m-d') . self::$extension);

    if (!isset(self::$fopens[$path]))
      if (!$fopen = @fopen($path, 'ab'))
        return false;
      else
        self::$fopens[$path] = $fopen;

    for($written = 0, $length = charsetStrlen($text); $written < $length; $written += $result)
      if (($result = fwrite(self::$fopens[$path], charsetSubstr($text, $written))) === false)
        break;

    $newfile && @chmod($path, self::$permissions);

    return is_int($result);
  }

  public static function info($msg) {
    $traces = ($traces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)) && ($traces = array_shift($traces)) && isset($traces['file'], $traces['line']) ? ' - ' . $traces['file'] . '(' . $traces['line'] . ')' : '';
    return self::msg(date(self::$dateFormat) . $traces . "\n" . str_repeat('—', 80) . "\n" . dump($msg) . "\n\n", 'log-info-');
  }

  public static function error($msg) {
    $traces = ($traces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)) && ($traces = array_shift($traces)) && isset($traces['file'], $traces['line']) ? ' - ' . $traces['file'] . '(' . $traces['line'] . ')' : '';
    return self::msg(date(self::$dateFormat) . $traces . "\n" . str_repeat('—', 80) . "\n" . dump($msg) . "\n\n", 'log-error-');
  }

  public static function warning($msg) {
    $traces = ($traces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)) && ($traces = array_shift($traces)) && isset($traces['file'], $traces['line']) ? ' - ' . $traces['file'] . '(' . $traces['line'] . ')' : '';
    return self::msg(date(self::$dateFormat) . $traces . "\n" . str_repeat('—', 80) . "\n" . dump($msg) . "\n\n", 'log-warning-');
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
  // public static function query($valid, $time, $sql, $values) {
  //   @self::msg(self::formatQuery(date(self::$config['dateFormat']), $valid, $time, $sql, $values), 'query-');
  //   return true;
  // }
  // private static function formatQuery($date, $valid, $time, $sql, $values) {
  //   self::$type || self::$type = ENVIRONMENT !== 'cmd' ? request_is_cli() ? cliColor('cli', 'c') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'C') : cliColor('web', 'p') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'P') : cliColor('cmd', 'y') . cliColor(' ➜ ', 'N') . cliColor(CMD_FILE, 'Y');
  //   return self::$type . cliColor(' │ ', 'N') . cliColor($date, 'w') . cliColor(' ➜ ', 'N') . cliColor($time, $time < 999 ? $time < 99 ? $time < 9 ? 'w' : 'W' : 'Y' : 'R') . '' . cliColor('ms', $time < 999 ? $time < 99 ? $time < 9 ? 'N' : 'w' : 'y' : 'r') . cliColor(' │ ', 'N') .($valid ? cliColor('OK', 'g') : cliColor('GG', 'r')) . cliColor(' ➜ ', 'N') . call_user_func_array('sprintf', array_merge(array(preg_replace_callback('/\?/', function($matches) { return cliColor('%s', 'W'); }, $sql)), $values)) . "\n";
  // }
}
