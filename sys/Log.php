<?php

class Log {
  private static $extension = '.log';
  private static $permissions = 0777;
  private static $dateFormat = 'Y-m-d H:i:s';
  private static $fopens = [];

  public static function message($text, $prefix = 'log-') {
    if (!(is_dir(PATH_LOG) && isReallyWritable(PATH_LOG)))
      return false;

    $newfile = !file_exists($path = PATH_LOG . $prefix . date('Y-m-d') . self::$extension);

    if (!isset(self::$fopens[$path]))
      if (!$fopen = @fopen($path, 'ab'))
        return false;
      else
        self::$fopens[$path] = $fopen;

    for($written = 0, $length = Charset::strlen($text); $written < $length; $written += $result)
      if (($result = fwrite(self::$fopens[$path], Charset::substr($text, $written))) === false)
        break;

    $newfile && @chmod($path, self::$permissions);

    return is_int($result);
  }

  private static function formatLine($date, $title, $msg) {
    return cliColor($date, 'w') . cliColor('：', 'N') . $title . cliColor('：', 'N') . $msg . "\n";
  }
  public static function info($msg) {
    return self::message(self::formatLine(date(self::$dateFormat), cliColor('紀錄', 'g'), $msg), 'log-info-');
  }
  public static function error($msg) {
    return self::message(self::formatLine(date(self::$dateFormat), cliColor('錯誤', 'r'), $msg), 'log-error-');
  }
  public static function warning($msg) {
    return self::message(self::formatLine(date(self::$dateFormat), cliColor('警告', 'y'), $msg), 'log-warning-');
  }
  public static function closeAll() {
    foreach(self::$fopens as $fopen) fclose($fopen);
    return true;
  }


  // public static function queryLine() {
  //   self::$type || self::$type = ENVIRONMENT !== 'cmd' ? request_is_cli() ? cliColor('cli', 'c') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'C') : cliColor('web', 'p') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'P') : cliColor('cmd', 'y') . cliColor(' ➜ ', 'N') . cliColor(CMD_FILE, 'Y');
  //   @self::message("\n" . self::$type . cliColor(' ╞' . str_repeat('═', CLI_LEN -(strlen(self::$type) - 31)) . "\n", 'N'), 'query-');
  //   return true;
  // }
  // public static function query($valid, $time, $sql, $values) {
  //   @self::message(self::formatQuery(date(self::$config['dateFormat']), $valid, $time, $sql, $values), 'query-');
  //   return true;
  // }
  // private static function formatQuery($date, $valid, $time, $sql, $values) {
  //   self::$type || self::$type = ENVIRONMENT !== 'cmd' ? request_is_cli() ? cliColor('cli', 'c') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'C') : cliColor('web', 'p') . cliColor(' ➜ ', 'N') . cliColor(URL::uriString(), 'P') : cliColor('cmd', 'y') . cliColor(' ➜ ', 'N') . cliColor(CMD_FILE, 'Y');
  //   return self::$type . cliColor(' │ ', 'N') . cliColor($date, 'w') . cliColor(' ➜ ', 'N') . cliColor($time, $time < 999 ? $time < 99 ? $time < 9 ? 'w' : 'W' : 'Y' : 'R') . '' . cliColor('ms', $time < 999 ? $time < 99 ? $time < 9 ? 'N' : 'w' : 'y' : 'r') . cliColor(' │ ', 'N') .($valid ? cliColor('OK', 'g') : cliColor('GG', 'r')) . cliColor(' ➜ ', 'N') . call_user_func_array('sprintf', array_merge(array(preg_replace_callback('/\?/', function($matches) { return cliColor('%s', 'W'); }, $sql)), $values)) . "\n";
  // }
}