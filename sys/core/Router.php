<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Router {

  private $work;
  private $controller;
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

  public function __construct() {
    $this->work    = null;
    
    // $this->befores      = [];
    // $this->beforeParams = [];

    // $this->afters      = [];
    // $this->afterParams = [];
  }
  
  public function work($work) {
    return $this->setWork($work);
  }
  
  public function controller($controller) {
    $this->controller = $controller;
    return $this;
  }
  
  public function __toString() {
    return '';
  }

  public function exec() {
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

    if (isset(self::$routers[$method]))
      foreach (self::$routers[$method] as $segment => $obj)
        if (preg_match ('#^' . $segment . '$#', implode('/', Url::segments()), $matches)) {

          $params = [];
          foreach (array_filter(array_keys($matches), 'is_string') as $key)
            self::$params[$key] = $matches[$key];

          return self::$current = $obj;
        }

    return self::$current = '';
  }

  public static function get($segment) {
    $segment = self::setSegment($segment);
    isset(self::$routers['get']) || self::$routers['get'] = [];
    return self::$routers['get'][$segment] = new Router('get', $segment);
  }
  
  public static function post($segment) {
    $segment = self::setSegment($segment);
    isset(self::$routers['post']) || self::$routers['post'] = [];
    return self::$routers['post'][$segment] = new Router('post', $segment);
  }
  
  public static function put($segment) {
    $segment = self::setSegment($segment);
    isset(self::$routers['put']) || self::$routers['put'] = [];
    return self::$routers['put'][$segment] = new Router('put', $segment);
  }
  
  public static function delete($segment) {
    $segment = self::setSegment($segment);
    isset(self::$routers['delete']) || self::$routers['delete'] = [];
    return self::$routers['delete'][$segment] = new Router('delete', $segment);
  }
  
  public static function cli($segment) {
    $segment = self::setSegment($segment);
    isset(self::$routers['cli']) || self::$routers['cli'] = [];
    return self::$routers['cli'][$segment] = new Router('cli', $segment);
  }

  public static function all() {
    return self::$routers;
  }
}

Router::init();
Router::current();
Router::current() || new GG('迷路惹！', 404);
