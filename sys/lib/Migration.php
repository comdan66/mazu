<?php defined('MAZU') || exit('此檔案不允許讀取！');

class _MigrationModel extends M\Model {}

class Migration {
  private static $obj;
  private static $gets;
  private static $files;

  private static function query($sql, $vals = [], $fetchModel = PDO::FETCH_ASSOC, $returnError = false) {
    return \_M\Connection::instance()->query($sql, $vals, $fetchModel, $returnError);
  }

  private static function runCreateSql($model) {
    $encoding = config('database', 'encoding');
    $dbcollat = $encoding ? $encoding . '_unicode_ci' : 'utf8mb4_unicode_ci';

    $sql = "CREATE TABLE `" . $model . "` ("
            . "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,"
            . "`version` varchar(5) COLLATE " . $dbcollat . " NOT NULL DEFAULT '0' COMMENT '版本',"
            . "`updateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',"
            . "`createAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',"
            . "PRIMARY KEY (`id`)"
          . ") ENGINE=InnoDB DEFAULT CHARSET=" . $encoding . " COLLATE=" . $dbcollat . ";";

    return self::query($sql);
  }

  public static function init() {
    $model = '_MigrationModel';
    
    self::$obj = null;
    self::$gets = [];
    self::$files = null;

    $model && class_exists($model) || gg('Migration 錯誤，找不到指定的 Model，Model：' . $model);

    $obj = self::query("SHOW TABLES LIKE '" . $model . "';")->fetch(PDO::FETCH_NUM);
    $obj && $obj[0] == $model || self::runCreateSql($model) || gg('Migration 錯誤，產生 ' . $model . ' 資料表失敗！');

    self::$obj = $model::first();
    self::$obj || self::$obj = $model::create(['version' => '0']);
    self::$obj || gg('Migration 初始化失敗！');
  }

  public static function nowVersion() {
    return $now = (int)self::$obj->version;
  }

  public static function files($re = false) {
    if (!$re && self::$files !== null)
      return self::$files;

    $files = array_filter(array_map(function($file) {
      return is_readable($file) && ($name = basename($file, '.php')) && preg_match('/^\d{3}-(.+)$/', $name) && ($v = sscanf($name, '%[0-9]+', $number) ? $number : 0) ? [(int) $v, $file] : null;
    }, glob(PATH_MIGRATION . '*-*.php')));

    $files = array_combine(array_column($files, 0), array_column($files, 1));
    ksort($files);

    return self::$files = $files;
  }

  public static function get($file, $isUp = null) {
    if (isset(self::$gets[$file]))
      return $isUp !== null ? $isUp ? self::$gets[$file]['up'] : self::$gets[$file]['down'] : self::$gets[$file];
    
    $data = include_once($file);

    isset($data['up'], $data['at'], $data['down']) && is_string($data['up']) && is_string($data['down']) && is_string($data['at']) || gg('Migration 錯誤，檔案結構格式錯誤！', '請檢查 up、down 以及 at 功能有缺！', 'File：' . $file);

    self::$gets[$file] = $data;
    return $isUp !== null ? $isUp ? self::$gets[$file]['up'] : self::$gets[$file]['down'] : self::$gets[$file];
  }

  private static function run($tmps, $isUp, $to) {
    $last = !$isUp && $to ? array_pop($tmps) : null;

    foreach ($tmps as $file) {
      if (($sql = self::get($file[1], $isUp)) && ($error = self::query($sql, [], PDO::FETCH_ASSOC, true)))
        return ['錯誤原因：' . cliColor($error, 'W'), "SQL 語法：" . cliColor($sql, 'W')];

      self::$obj->version = $file[0];
      self::$obj->save();
    }
    
    if ($isUp)
      return true;

    $version = $last ? $last[0] : 0;

    self::$obj->version = $version;
    self::$obj->save();

    return true;
  }

  public static function to($to = null) {
    $now = self::nowVersion();
    $files = self::files();

    $tmps = array_keys($files);
    $to !== null || $to = end($tmps);

    if ($to == $now)
      return true;

    $tmps = [];

    if ($isUp = $to > $now)
      foreach ($files as $version => $file)
        $version > $now && $version <= $to && array_push($tmps, [$version, $file]);
    else
      foreach ($files as $version => $file)
        $version <= $now && $version >= $to && array_unshift($tmps, [$version, $file]);

    return self::run($tmps, $isUp, $to);
  }
}

Migration::init();