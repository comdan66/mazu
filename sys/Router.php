<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Router {
  private $method;
  private $segment;
  private $work;
  private $params;

  private $befores;
  private $beforeParams;

  private $afters;
  private $afterParams;

  private $status;

  private static $current;
  private static $routers;
  
  public function __construct($method, $segment) {
    $this->method  = $method;
    $this->segment = $segment;
    $this->work    = null;
    $this->status  = 200;
    $this->params  = [];
    
    $this->befores      = [];
    $this->beforeParams = [];

    $this->afters      = [];
    $this->afterParams = [];
  }

  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  public function getStatus() {
    return $this->status;
  }
  
  public function setParams($params) {
    $this->params = $params;
    return $this;
  }
  
  public function getParams($key = null) {
    return $key !== null && isset($this->params[$key]) ? $this->params[$key] : $this->params;
  }
  
  public function before($before) {
    array_push($this->befores, $before);
    return $this;
  }

  public function setWork($work) {
    $this->work = $work;
    return $this;
  }
  
  public function work($work) {
    return $this->setWork($work);
  }

  public function getBeforeParams($key = null) {
    return $key !== null && isset($this->beforeParams[$key]) ? $this->beforeParams[$key] : $this->beforeParams;
  }
  
  public function after($after) {
    array_push($this->afters, $after);
    return $this;
  }

  public function getAfterParams($key = null) {
    return $key !== null && isset($this->afterParams[$key]) ? $this->afterParams[$key] : $this->afterParams;
  }

  public function exec() {
    foreach ($this->befores as $before)
      array_push($this->beforeParams, $before());
    
    if ($this->work === null)
      return null;

    if (is_string($this->work))
      return $this->work;

    if (is_array($this->work))
      return $this->work;

    if (is_callable($this->work) && ($tmp = $this->work))
      return $tmp();

    foreach ($this->afters as $after)
      array_push($this->afterParams, $before());
  }

  public static function init() {
    self::$current = null;
    self::$routers = [];
    Load::path(PATH_APP . 'Routers.php');
  }

  private static function setSegment($segment) {
    $segment = trim($segment, '/');
    return preg_replace('/\(([^\[]+)\[/', '(?<$1>[', str_replace([':any', ':num'], ['[^/]+', '[0-9]+'], $segment));
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

  public static function current() {
    return self::$current;
  }
  
  public static function all() {
    return self::$routers;
  }
  
  public static function getMatchRouter() {
    $method = strtolower (isCli() ? 'cli' : (isset ($_POST['_method']) ? $_POST['_method'] : (isset ($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get')));

    if (isset(self::$routers[$method]))
      foreach (self::$routers[$method] as $segment => $obj)
        if (preg_match ('#^' . $segment . '$#', implode('/', Url::segments()), $matches)) {
          
          $params = [];
          foreach (array_filter(array_keys($matches), 'is_string') as $key)
            $params[$key] = $matches[$key];
          
          self::$routers = [];

          return self::$current = $obj->setParams($params);
        }

    return null;
  }
}

Router::init();
Router::getMatchRouter();
Router::current() || gg('迷路惹！', 404);
