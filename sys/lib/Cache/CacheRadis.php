<?php defined('MAZU') || exit('此檔案不允許讀取！');

class CacheRadis extends Cache {
  private $redis = null;

  private $host = null; 
  private $port = null; 
  private $timeout = null; 
  private $password = null; 
  private $database = null; 
  private $serializeKey = '';

  public function __construct ($options = []) {
    parent::__construct($options);

    extension_loaded('redis') || gg('CacheRadis 錯誤，載入 Redis 失敗。');

    $this->host = isset($options['host']) ? $options['host'] : 'localhost';
    $this->port = isset($options['port']) ? $options['port'] : '6379';
    $this->timeout = isset($options['timeout']) ? $options['timeout'] : null;
    $this->password = isset($options['password']) ? $options['password'] : null;
    $this->database = isset($options['database']) ? $options['database'] : null;
    $this->serializeKey = isset($options['serializeKey']) ? $options['serializeKey'] : '_mazu_cache_serialized';

    $this->redis = new Redis();
    $this->redis->connect ($this->host, $this->port, $this->timeout) || gg('CacheRadis 錯誤，連不上 Redis，Host：' . $this->host . '，Port：' . $this->port . '，Timeout：' . $this->timeout);
    if ($this->password) $this->redis->auth($this->password) || gg('CacheRadis 錯誤，請確認密碼，密碼：' . $this->password);
    if ($this->database) $this->redis->select($this->database) || gg('CacheRadis 錯誤，找不到指定的 Database，Database：' . $this->database);

    $serialized = $this->redis->sMembers($this->prefix . $this->serializeKey);
    empty($serialized) || $this->serialized = array_flip($serialized);
  }

  public function get($id) {
    if (($value = $this->redis->get($this->prefix . $id)) === false)
      return null;

    if (isset($this->serialized[$this->prefix . $id]))
      return unserialize($value);

    return $value;
  }

  public function save($id, $data, $ttl = 60) {
    $id = $this->prefix . $id;

    if (is_array($data) || is_object($data)) {
      if (!$this->redis->sIsMember($this->prefix . $this->serializeKey, $id) && !$this->redis->sAdd($this->prefix . $this->serializeKey, $id))
        return false;

      isset($this->serialized[$id]) || $this->serialized[$id] = true;
      $data = serialize($data);
    } else if (isset($this->serialized[$id])) {
      $this->serialized[$id] = null;
      $this->redis->sRemove($this->prefix . $this->serializeKey, $id);
    }

    return $this->redis->set($id, $data, $ttl) ? true : false;
  }

  public function delete($id) {
    $id = $this->prefix . $id;
    if ($this->redis->delete($id) !== 1)
      return false;

    if (isset($this->serialized[$id])) {
      $this->serialized[$id] = null;
      $this->redis->sRemove($this->prefix . $this->serializeKey, $id);
    }

    return true;
  }

  public function clean () {
    if (!$this->prefix)
      return $this->redis->flushDB();

    if ($keys = $this->redis->keys($this->prefix . '*'))
      foreach ($keys as $key)
        $this->redis->delete($keys);
    
    return true;
  }

  public function info() {
    return $this->redis->info();
  }

  public function metadata($key) {
    if (($value = $this->get($key)) === false)
      return null;

    return array (
      'expire' => time() + $this->redis->ttl($key),
      'data' => $value
    );
  }

  public function __destruct() {
    $this->redis && $this->redis->close() && $this->redis = null;
  }
}
