<?php defined('MAZU') || exit('此檔案不允許讀取！');

class SessionRedis extends Session implements SessionHandlerInterface {
  private $redis = null; 
  private $prefix = 'mazu_session:'; 
  private $lockKey = null; 
  private $keyExists = false; 
  
  private $host = null; 
  private $port = null; 
  private $timeout = null; 
  private $password = null; 
  private $database = null; 

  public function __construct() {
    parent::__construct();

    $this->host = 'localhost';
    $this->port = '6379';

    extension_loaded('redis') || gg('載入 SessionRedis 失敗，無 Redis 函式！');
    $this->prefix = $this->prefix . (Session::matchIp() ? Input::ip() . ':' : '');
  }

  public function open($path, $name) {
    if ($this->redis !== null)
      return $this->succ();

    $this->redis = new Redis();
    $this->redis->connect($this->host, $this->port, $this->timeout) || gg('SessionRedis 錯誤，連不上 Redis，Host：' . $this->host . '，Port：' . $this->port . '，Timeout：' . $this->timeout);
    if ($this->password) $this->redis->auth($this->password) || gg('SessionRedis 錯誤，請確認密碼，密碼：' . $this->password);
    if ($this->database) $this->redis->select($this->database) || gg('SessionRedis 錯誤，找不到指定的 Database，Database：' . $this->database);

    return $this->succ();
  }

  public function read($sessionId) {
    if ($this->redis && $this->getLock($sessionId)) {
      $this->sessionId = $sessionId;
      $data = $this->redis->get($this->prefix . $sessionId);

      is_string($data) ? $this->keyExists = true : $data = '';
      $this->fingerPrint = md5($data);
      return $data;
    }

    return $this->fail();
  }

  public function write($sessionId, $sessionData) {
    if (!($this->redis && $this->lockKey))
      return $this->fail();

    if ($sessionId !== $this->sessionId) {
      if (!($this->releaseLock() && $this->getLock($sessionId)))
        return $this->fail();

      $this->keyExists = false;
      $this->sessionId = $sessionId;
    }
    
    $this->redis->setTimeout($this->lockKey, 300);

    if ($this->fingerPrint !== ($fingerPrint = md5($sessionData)) || $this->keyExists === false) {
      if ($this->redis->set($this->prefix . $sessionId, $sessionData, self::expiration())) {
        $this->fingerPrint = $fingerPrint;
        $this->keyExists = true;
        return $this->succ();
      }

      return $this->fail();
    }
    return $this->redis->setTimeout($this->prefix . $sessionId, self::expiration()) ? $this->succ() : $this->fail();
  }

  public function close() {
    if ($this->redis === null)
      return $this->succ();

    try {
      if ($this->redis->ping() === '+PONG') {
        $this->releaseLock();

        if ($this->redis->close() === false)
          return $this->fail();
      }
    } catch (RedisException $e) {
      Log::error('Session 錯誤！', 'SessionRedis close() 時錯誤！', '錯誤訊息：' . $e->getMessage());
    }

    $this->redis = null;

    return $this->succ();
  }

  public function destroy($sessionId) {
    if (!($this->redis && $this->lockKey))
      return $this->fail();

    $this->redis->delete($this->prefix . $sessionId);
    $this->cookieDestroy();
    return $this->succ();
  }

  public function gc($maxLifeTime) {
    return $this->succ();
  }

  protected function getLock($sessionId) {
    if ($this->lockKey === $this->prefix . $sessionId . ':lock')
      return $this->redis->setTimeout($this->lockKey, 300);

    $attempt = 0;
    $lockKey = $this->prefix . $sessionId . ':lock';

    do {
      $ttl = $this->redis->ttl($lockKey);
      if ($ttl > 0) {
        sleep(1);
        continue;
      }

      if (!$result = ($ttl === -2) ? $this->redis->set($lockKey, time(), ['nx', 'ex' => 300]) : $this->redis->setex($lockKey, 300, time()))
        return false;

      $this->lockKey = $lockKey;

      break;
    } while (++$attempt < 30);

    if ($attempt === 30)
      return false;

    return $this->lock = true;
  }

  protected function releaseLock() {
    if ($this->redis && $this->lockKey && $this->lock) {
      if (!$this->redis->delete($this->lockKey))
        return false;

      $this->lockKey = null;
      $this->lock = false;
    }

    return true;
  }
  
  public function __destruct() {
    $this->redis && $this->redis->close() && $this->redis = null;
  }
}
