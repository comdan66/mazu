<?php defined('MAZU') || exit('此檔案不允許讀取！');

if (!interface_exists ('SessionHandlerInterface', false)) {
  interface SessionHandlerInterface {
    public function open($savePath, $name);
    public function close();
    public function read($sessionId);
    public function write($sessionId, $sessionData);
    public function destroy($sessionId);
    public function gc($maxlifetime);
  }
}

abstract class Session {
  
  // protected $sessionId = '';
  protected $fingerPrint = '';
  protected $lock = false;
  protected $success, $failure;

  protected function __construct() {
    if (isPhpVersion('7')) {
      $this->success = true;
      $this->failure = false;
    } else {
      $this->success = 0;
      $this->failure = -1;
    }
  }

  // protected function getLock($sessionId) { return $this->lock = true; }
  // protected function releaseLock() { return !($this->lock && $this->lock = false); }
  protected function cookieDestroy() { return setcookie(self::$cookie['name'], null, 1, self::$cookie['path'], self::$cookie['domain'], self::$cookie['secure'], true); }
  protected function succ() { return $this->success; }
  protected function fail() { return $this->failure; }
















  private static $matchIp;
  private static $expiration;
  private static $time2Update;
  private static $regenerateDestroy;
  private static $cookieName;
  private static $cookie;
  private static $sessionIdRegexp;
  private static $lastRegenerateKey;
  private static $varsKey;


  protected static function matchIp() {
    return self::$matchIp;
  }

  protected static function cookieName() {
    return self::$cookie['name'];
  }

  public static function init() {
    if (isCli ())
      return null;

    if ((bool)ini_get('session.auto_start'))
      return null;

    self::$matchIp           = config('session', 'matchIp');
    self::$expiration        = 60 * 60 * 24 * 30 * 3; // 存活週期 // 單位秒 // 三個月
    self::$time2Update       = 60 * 60 * 24; // 更新 session ID 週期 // 每天更新
    self::$regenerateDestroy = true; // 重置 key 是否刪除舊的
    self::$lastRegenerateKey = '__mazu_last_regenerate';
    self::$varsKey           = '__mazu_vars';

    $cookie = config('cookie');
    self::$cookie['name'] = 'mazu_session';
    self::$cookie['path'] = $cookie['path'];
    self::$cookie['domain'] = $cookie['domain'];
    self::$cookie['secure'] = $cookie['secure'];

    $driver = config('session', 'driver');
    Load::sysLib('Session' . DIRECTORY_SEPARATOR . $driver . '.php') || gg('載入 Session Driver 失敗！');

    $class = new $driver();
    session_set_save_handler($class, true);

    self::configure();

    if (isset($_COOKIE[self::$cookie['name']]) && !(is_string($_COOKIE[self::$cookie['name']]) && preg_match('#\A' . self::sessionIdRegexp() . '\z#', $_COOKIE[self::$cookie['name']])))
      unset($_COOKIE[self::$cookie['name']]);

    session_start();

    if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') && self::$time2Update > 0) {
      if (!isset($_SESSION[self::$lastRegenerateKey]))
        $_SESSION[self::$lastRegenerateKey] = time();
      else if ($_SESSION[self::$lastRegenerateKey] < (time() - self::$time2Update))
        self::sessRegenerate(self::$regenerateDestroy);

    } else if (isset($_COOKIE[self::$cookie['name']]) && $_COOKIE[self::$cookie['name']] === session_id()) {
      setcookie(self::$cookie['name'],
        session_id(),
        self::$expiration ? time() + self::$expiration : 0,
        self::$cookie['path'],
        self::$cookie['domain'],
        self::$cookie['secure'],
        true);
    }

    self::oaciInitVars();
  }

  private static function configure() {
    ini_set('session.name', self::$cookie['name']);

    session_set_cookie_params(self::$expiration, self::$cookie['path'], self::$cookie['domain'], self::$cookie['secure'], true);

    if (self::$expiration)
      ini_set('session.gc_maxlifetime', self::$expiration = (int)self::$expiration);
    else
      self::$expiration = (int)ini_get('session.gc_maxlifetime');

    // Security is king
    ini_set('session.use_trans_sid', 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);

    self::configureSessionIdLength();
  }

  protected static function sessionIdRegexp() {
    return self::$sessionIdRegexp;
  }

  private static function configureSessionIdLength() {
    if (PHP_VERSION_ID < 70100) {
      $hashFunc = ini_get('session.hash_function');

      if (ctype_digit($hashFunc)) {
        $hashFunc === '1' || ini_set('session.hash_function', 1);
        $bits = 160;
      } else if (!in_array($hashFunc, hash_algos(), true)) {
        ini_set('session.hash_function', 1);
        $bits = 160;
      } else if (($bits = strlen(hash($hashFunc, 'dummy', false)) * 4) < 160) {
        ini_set('session.hash_function', 1);
        $bits = 160;
      }

      $bitsPerCharacter = (int)ini_get('session.hash_bits_per_character');
      $sidLength        = (int)ceil($bits / $bitsPerCharacter);
    } else {
      $bitsPerCharacter = (int)ini_get('session.sid_bits_per_character');
      $sidLength        = (int)ini_get('session.sid_length');
      
      if (($bits = $sidLength * $bitsPerCharacter) < 160) {
        $sidLength += (int)ceil((160 % $bits) / $bitsPerCharacter);
        ini_set('session.sid_length', $sidLength);
      }
    }

    switch ($bitsPerCharacter) {
      case 4: self::$sessionIdRegexp = '[0-9a-f]';      break;
      case 5: self::$sessionIdRegexp = '[0-9a-v]';      break;
      case 6: self::$sessionIdRegexp = '[0-9a-zA-Z,-]'; break;
    }

    self::$sessionIdRegexp .= '{' . $sidLength . '}';
  }

  private static function oaciInitVars() {
    isset($_SESSION[self::$varsKey]) || $_SESSION[self::$varsKey] = [];

    if (!$_SESSION[self::$varsKey])
      return ;

    $current_time = time();

    foreach ($_SESSION[self::$varsKey] as $key => &$value) {
      if ($value === 'new')
        $_SESSION[self::$varsKey][$key] = 'old';
      else if ($value < $current_time)
        unset($_SESSION[$key], $_SESSION[self::$varsKey][$key]);
    }

    $_SESSION[self::$varsKey] || $_SESSION[self::$varsKey] = [];
  }

  public static function sessDestroy() {
    return session_destroy();
  }

  public static function sessRegenerate($destroy = false) {
    $_SESSION[self::$lastRegenerateKey] = time();
    return session_regenerate_id($destroy);
  }

  public static function allData() {
    return $_SESSION;
  }

  public static function hasData($key) {
    return isset($_SESSION[$key]);
  }

// -------------------------------------

  public static function setData($data, $value) {
    $_SESSION[$data] = $value;
    return true;
  }

  public static function getData($key) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
  }

  public static function unsetData($key) {
    is_array($key) || $key = [$key];

    foreach ($key as $k)
      unset($_SESSION[$k]);

    return true;
  }

// -------------------------------------

  public static function getFlashDataKeys() {
    if (!isset($_SESSION[self::$varsKey]))
      return [];

    return array_values(array_filter(array_keys($_SESSION[self::$varsKey]), function($key) {
      return !is_int($_SESSION[self::$varsKey][$key]);
    }));
  }

  public static function getFlashDatas() {
    if (!isset($_SESSION[self::$varsKey]))
      return [];

    $flashdata = [];

    if ($_SESSION[self::$varsKey])
      foreach ($_SESSION[self::$varsKey] as $key => $value)
        if (!is_int($value) && isset($_SESSION[$key]))
          $flashdata[$key] = $_SESSION[$key];

    return $flashdata;
  }

  public static function getFlashData($key) {
    return isset($_SESSION[self::$varsKey], $_SESSION[self::$varsKey][$key], $_SESSION[$key]) && !is_int($_SESSION[self::$varsKey][$key]) ? $_SESSION[$key] : null;
  }

  public static function setFlashData($data, $value) {
    return self::setData($data, $value) && self::markAsFlash($data);
  }

  public static function markAsFlash($key) {
    if (!isset($_SESSION[$key]))
      return false;

    $_SESSION[self::$varsKey][$key] = 'new';
    return true;
  }

  public static function keepFlashData($key) {
    return self::markAsFlash($key);
  }

  public static function unmarkFlashData($key) {
    if (!isset($_SESSION[self::$varsKey]))
      return true;

    is_array($key) || $key = [$key];

    foreach ($key as $k)
      if (isset($_SESSION[self::$varsKey][$k]) && !is_int($_SESSION[self::$varsKey][$k]))
        unset($_SESSION[self::$varsKey][$k]);

    $_SESSION[self::$varsKey] || $_SESSION[self::$varsKey] = [];

    return true;
  }

  public static function unsetFlashData($key) {
    return self::unmarkFlashData($key) && self::unsetData($key);
  }

// -------------------------------------

  public static function getTmpKeys() {
    if (!isset($_SESSION[self::$varsKey]))
      return [];

    return array_values(array_filter(array_keys($_SESSION[self::$varsKey]), function($key) {
      return is_int($_SESSION[self::$varsKey][$key]);
    }));

    return $keys;
  }

  public static function getTmpDatas() {
    $tempdata = [];

    if ($_SESSION[self::$varsKey])
      foreach ($_SESSION[self::$varsKey] as $key => &$value)
        if (is_int($value))
          $tempdata[$key] = $_SESSION[$key];

    return $tempdata;
  }

  public static function setTmpData($data, $value, $ttl = 300) {
    return self::setData($data, $value) && self::markAsTmp($data, $ttl);
  }

  public static function getTmpData($key) {
    return isset($_SESSION[self::$varsKey], $_SESSION[self::$varsKey][$key], $_SESSION[$key]) && is_int($_SESSION[self::$varsKey][$key]) ? $_SESSION[$key] : null;
  }

  public static function markAsTmp($key, $ttl = 300) {
    if (!isset($_SESSION[$key]))
      return false;
    
    $_SESSION[self::$varsKey][$key] = time() + $ttl;
    return true;
  }

  public static function unmarkTmp($key) {
    if (!isset($_SESSION[self::$varsKey]))
      return true;

    is_array($key) || $key = [$key];

    foreach ($key as $k)
      if (isset($_SESSION[self::$varsKey][$k]) && is_int($_SESSION[self::$varsKey][$k]))
        unset($_SESSION[self::$varsKey][$k]);

    $_SESSION[self::$varsKey] || $_SESSION[self::$varsKey] = [];

    return true;
  }

  public static function unsetTempData($key) {
    return self::unmarkTmp($key) && self::unsetData($key);
  }
}

Session::init ();