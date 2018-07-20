<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Router {

  private $work;
  private $cmd;
  private $name;
  private $segment;
  private $dirs;

  private $path;
  private $class;
  private $method;
  // private $befores;
  // private $beforeParams;

  // private $afters;
  // private $afterParams;

  private static $current;
  private static $routers;

  // private static $method;
  // private static $segment;
  private static $params;
  private static $status;
  private static $requestMethod;
  private static $className;
  private static $methodName;
  private static $names;
  
  public static function init() {
    self::$current = null;
    self::$routers = [];
    self::$params = [];
    self::$status = 200;
    self::$requestMethod = null;
    self::$className = null;
    self::$methodName = null;
    self::$names = [];

    Load::app('routers.php');

    // // 使用 Cache
    // Load::sysLib('Cache.php');
    // self::$routers = Cache::cacheFile('Routers', function() {
    //   Load::app('routers.php');
    //   return Router::all();
    // }, 10);
  }

  public function __construct($segment = null, $dirs = []) {
    // $this->controller = null;
    $this->work = null;
    $this->cmd = null;
    $this->dirs = $dirs;

    $this->path = null;
    $this->class = null;
    $this->method = null;

    $this->segment = $segment;
    // $this->controller
    // $this->name();
    
    // $this->befores      = [];
    // $this->beforeParams = [];

    // $this->afters      = [];
    // $this->afterParams = [];
  }
  
  public function work($work) {
    return $this->setWork($work);
  }
  
  public function controller($controller) {
    $controller = trim($controller, '/');

    strpos($controller, '@') !== false || $controller = $controller . '@' . 'index';
    list($this->path, $this->method) = explode('@', $controller);
    $this->class = pathinfo($this->path, PATHINFO_BASENAME);
    
    $this->path = pathinfo($this->path, PATHINFO_DIRNAME);
    $this->path = $this->dirs['dir'] . ($this->path === '.' ? '' : $this->path . '/');

    return $this->name($this->class . ucfirst($this->method));
  }
  
  public function alias($name) {
    return $this->name($name);
  }

  public function name($name = null) {
    if ($name === null)
      return $this->name;

    $this->name = $this->dirs['prefix'] . $name;
    return isset(self::$names[$this->name]) ? self::$names[$this->name] : self::$names[$this->name] = $this;
  }
  
  
  public function segment($segment = null) {
    if ($segment === null)
      return $this->segment;

    $this->segment = $segment;
    return $this;
  }
  
  public function cmd($cmd) {
    $this->cmd = $cmd;
    return $this;
  }
  
  public function __toString() {
    return '';
  }

  public function exec() {
    if ($this->cmd !== null) {
      return Load::file(PATH_SYS_CMD . $this->cmd . '.php') || gg('找不到指定的 CMD 檔案！', '檔案位置：' . PATH_SYS_CMD . $this->cmd . '.php');
    }

    if (isset($this->path, $this->class, $this->method)) {
      self::$className = $this->class;
      $path = $this->path . self::$className . '.php';
      Load::controller($path) || gg('找不到指定的 Class', 'Class：' . $this->class, '檔案位置：' . $path);
      class_exists(self::$className) || gg('找不到指定的 Class，請檢查 ' . $path . ' 檔案的 Class 名稱是否正確！', 'Class：' . self::$className, '檔案位置：' . $path);

      self::$methodName = $this->method;
      $obj = new self::$className();

      if ($error = $obj->constructError())
        return new GG($error, 500);
      else
        return call_user_func_array([$obj, self::$methodName], static::params());
    }

    // foreach ($this->befores as $before)
    //   array_push($this->beforeParams, $before());
    
    // if ($this->work === null)
    //   return null;

    // if (is_string($this->work))
      // return $this->work;

    // if (is_array($this->work))
    //   return $this->work;

    // if (is_callable($this->work) && ($tmp = $this->work))
    //   return $tmp();

    // foreach ($this->afters as $after)
    //   array_push($this->afterParams, $before());
  }

  public static function params($key = null) {
    return $key !== null ? array_key_exists($key, self::$params) ? self::$params[$key] : null : self::$params;
  }

  public static function className() {
    return self::$className;
  }

  public static function methodName() {
    return self::$methodName;
  }

  public static function setStatus($status = 200) {
    return self::$status = $status;
  }

  public static function requestMethod() {
    return self::$requestMethod !== null ? self::$requestMethod : self::$requestMethod = strtolower (isCli() ? 'cli' : (isset ($_POST['_method']) ? $_POST['_method'] : (isset ($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get')));
  }

  public static function status() {
    return self::$status;
  }

  private static function setSegment($segment) {
    $segment = trim($segment, '/');
    return preg_replace('/\(([^\[]+)\[/', '(?<$1>[', str_replace([':any', ':num'], ['[^/]+', '[0-9]+'], $segment));
  }
  
  public static function current() {
    if (self::$current !== null)
      return self::$current === '' ? null : self::$current;

    $method = self::requestMethod();
    
    if ($method == 'cli' && defined('CMD')) {
      self::$current = new Router();
      self::$params = Url::segments();
      return self::$current->cmd(CMD);

    } else if (isset(self::$routers[$method]))
      foreach (self::$routers[$method] as $segment => $obj)
        if (preg_match ('#^' . $segment . '$#', implode('/', Url::segments()), $matches)) {

          $params = [];
          foreach (array_filter(array_keys($matches), 'is_string') as $key)
            self::$params[$key] = $matches[$key];

          return self::$current = $obj;
        }

    return self::$current = '';
  }

  private static function getDirs() {
    $dirs = array_filter(array_map(function($trace) { return isset($trace['class']) && ($trace['class'] == 'Router') && isset($trace['function']) && ($trace['function'] == 'dir') && isset($trace['type']) && ($trace['type'] == '::') && isset($trace['args'][0], $trace['args'][1]) ? ['dir' => trim($trace['args'][0], '/') . '/', 'prefix' => is_string($trace['args'][1]) ? $trace['args'][1] : ''] : null; }, debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)));
    $dirs = array_shift($dirs);
    $dirs || $dirs = ['dir' => '', 'prefix' =>''];
    $dirs['prefix'] || $dirs['prefix'] = implode('', array_map(function($t) { return ucfirst($t); }, explode('/', preg_replace ('/[\s_]+/', '/', $dirs['dir']))));
    return $dirs;
  }

  public static function __callStatic ($name, $args) {
    if (!in_array($name = strtolower($name), ['get', 'post', 'put', 'delete', 'cli']))
      return false;

    $args || $args = [''];
    $segment = array_shift($args);

    $dirs = self::getDirs();
    
    $segment = self::setSegment($segment);
    $segment = trim($dirs['dir'] . $segment, '/');
    isset(self::$routers[$name]) || self::$routers[$name] = [];
    return self::$routers[$name][$segment] = new Router($segment, $dirs);
  }

  public static function all() {
    return self::$routers;
  }
  
  public static function dir($dir, $prefix, $closure = null) {
    if (is_callable($prefix)) {
      $closure = $prefix;
      $prefix = '';
    }

    $closure();
  }

  public static function file($name) {
    return Load::router($name);
  }

  public static function findByName($name) {
    if (isset(self::$names[$name]))
      return self::$names[$name];

    foreach (self::$routers as $method => $routers)
      foreach ($routers as $segment => $router) {
        if ($segment === self::setSegment($name) || $router->name() === $name)
          return $router;
      }

    return null;
  }
}

Router::init();
Router::current();
Router::current() || new GG('迷路惹！', 404);
