<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Router {

  private $work;
  private $controller;
  private $cmd;
  private $name;
  private $segment;
  private $dirs;
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
  
  public static function init() {
    self::$current = null;
    self::$routers = [];
    self::$params = [];
    self::$status = 200;
    self::$requestMethod = null;
    self::$className = null;
    self::$methodName = null;
    Load::app('routers.php');
  }

  public function __construct($segment, $dirs) {
    $this->controller = null;
    $this->work = null;
    $this->cmd = null;
    $this->dirs = $dirs;

    $this->name = $segment;
    $this->segment = $segment;
    
    // $this->befores      = [];
    // $this->beforeParams = [];

    // $this->afters      = [];
    // $this->afterParams = [];
  }
  
  public function work($work) {
    return $this->setWork($work);
  }
  
  public function controller($controller) {
    $this->controller = $this->dirs['dir'] . $controller;
    
    return $this;
  }
  
  public function alias($name) {
    $this->name = $this->dirs['prefix'] . $name;
    return $this;
  }

  public function name($name = null) {
    if ($name === null)
      return $this->name;

    $this->name = $this->prefix . $name;
    return $this;
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

      Load::file(PATH_SYS_CMD . $this->cmd . '.php') || gg('找不到指定的 CMD 檔案！', '檔案位置：' . PATH_SYS_CMD . $this->cmd . '.php');
      // class_exists(self::$className) || gg('找不到指定的 Controller', 'Class：' . self::$className);
      return '';
    }
    if ($this->controller !== null) {
      strpos($this->controller, '@') !== false || gg('Controller 設定有誤！');
      list($path, self::$methodName) = explode('@', $this->controller);
      self::$className = pathinfo($path, PATHINFO_BASENAME);

      Load::controller($path . '.php') || gg('找不到指定的 Controller', '檔案位置：' . $path . '.php');
      class_exists(self::$className) || gg('找不到指定的 Controller', 'Class：' . self::$className);
      self::$methodName || gg('請設定 method！');

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

    return $dirs;
  }

  public static function __callStatic ($name, $args) {
    if (!in_array($name = strtolower($name), ['get', 'post', 'put', 'delete', 'cli']))
      return false;

    $args || gg('Router 的「' . $name . '」requset method 必須給予 Segment');
    $segment = array_shift($args);

    $dirs = self::getDirs();
    
    $segment = $dirs['dir'] . self::setSegment($segment);
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

  public static function findByName($name) {
    foreach (self::$routers as $method => $routers)
      foreach ($routers as $segment => $router)
        if ($router->segment() === self::setSegment($name) || $router->name() === $name)
          return $router;

    return null;
  }
}

Router::init();
Router::current();
Router::current() || new GG('迷路惹！', 404);
