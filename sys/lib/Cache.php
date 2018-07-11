<?php defined('MAZU') || exit('此檔案不允許讀取！');

abstract class Cache {
  protected $prefix = '';

  abstract public function get($id);
  abstract public function save($id, $data, $ttl);

  protected function __construct($options) {
    $this->prefix = isset($options['prefix']) ? $options['prefix'] : '';
  }

  private static $drivers = [];

  public static function create($driver, $options = []) {
    if (!empty(self::$drivers[$driver]))
      return self::$drivers[$driver];

    in_array($driver, ['CacheFile', 'CacheRadis']) || gg('Cache Driver 錯誤！');
    Load::sysLib('Cache' . DIRECTORY_SEPARATOR . $driver . '.php') || gg('載入 Cache Driver 失敗！');

    return self::$drivers[$driver] = new $driver($options);
  }
  public static function __callStatic($method, $args = []) {
    if (!$args)
      return null;

    $key = array_shift($args);
    if (($closure = array_shift($args)) === null)
      return null;

    is_numeric($expire = array_shift($args)) || $expire = 60;

    if (!$class = self::create($method))
      return is_callable($closure) ? $closure() : $closure;

    if (($data = $class->get($key)) !== null)
      return $data;

    $class->save($key, $data = is_callable($closure) ? $closure () : $closure, $expire);

    return $data;
  }
}
