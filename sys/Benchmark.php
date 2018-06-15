<?php

class Benchmark {
  private static $times = [];
  private static $memories = [];

  public static function markStar ($key) {
    isset (self::$times[$key]) || self::$times[$key] = [];
    isset (self::$memories[$key]) || self::$memories[$key] = [];

    self::$times[$key]['s'] = microtime (true);
    self::$memories[$key]['s'] = memory_get_usage ();
  }
  public static function markEnd ($key) {
    isset (self::$times[$key]) || self::$times[$key] = [];
    isset (self::$memories[$key]) || self::$memories[$key] = [];

    self::$times[$key]['e'] = microtime (true);
    self::$memories[$key]['e'] = memory_get_usage ();
  }

  public static function memoryUsage ($decimals = 4) {
    return round (memory_get_usage () / pow (1024, 2), 4) . 'MB';
  }

  public static function elapsedTime ($key = null, $decimals = 4) {
    if ($key === null) {
      $arr = [];
      foreach (self::$times as $key => $time)
        if (isset ($time['s']))
          $arr[$key] = number_format ((isset ($time['e']) ? $time['e'] : microtime (true)) - $time['s'], $decimals);
      return $arr;
    }

    if (!isset (self::$times[$key], self::$times[$key]['s']))
      return null;

    isset (self::$times[$key]['e']) || self::$times[$key]['e'] = microtime (true);

    return number_format (self::$times[$key]['e'] - self::$times[$key]['s'], $decimals);
  }

  public static function elapsedMemory ($key = null, $decimals = 4) {
    if ($key === null) {
      $arr = [];
      foreach (self::$memories as $key => $memory)
        if (isset ($memory['s']))
          $arr[$key] = round (((isset ($memory['e']) ? $memory['e'] : memory_get_usage ()) - $memory['s']) / pow (1024, 2), $decimals) . 'MB';
      return $arr;
    }

    if (!isset (self::$memories[$key], self::$memories[$key]['s']))
      return null;

    isset (self::$memories[$key]['e']) || self::$memories[$key]['e'] = memory_get_usage ();

    return round ((self::$memories[$key]['e'] - self::$memories[$key]['s']) / pow (1024, 2), $decimals) . 'MB';
  }
}
