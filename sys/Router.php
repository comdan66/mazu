<?php

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
  private static $routers = [];
  
  public function __construct($method, $segment) {
    $this->method = $method;
    $this->segment = $segment;
    $this->work = null;
    $this->status = 200;
    $this->params = [];
    
    $this->befores = [];
    $this->beforeParams = [];

    $this->afters = [];
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
  public function getBeforeParams($key = null) {
    return $key !== null && isset($this->beforeParams[$key]) ? $this->beforeParams[$key] : $this->beforeParams;
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
  public function before($before) {
    array_push($this->befores, $before);
    return $this;
  }
  public function after($after) {
    array_push($this->afters, $after);
    return $this;
  }

  public function setWork($work) {
    $this->work = $work;
    return $this;
  }
  public function work($work) {
    return $this->setWork($work);
  }













  public static function init() {
    Load::path(PATH_APP . 'Routers.php');
  }

  private static function setSegment($segment) {
    $segment = trim($segment, '/');
    $segment = str_replace(array (':any', ':num'), array ('[^/]+', '[0-9]+'), $segment);
    return preg_replace('/\(([^\[]+)\[/', '(?<$1>[', $segment);
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

// class Router {
//   private static $directories;
//   private static $class;
//   private static $method;
//   private static $params;
//   static $routers;
//   static $router;

//   public static function init ($routing = null) {
//     self::$router = self::$class = self::$method = null;
//     self::$routers = self::$params = self::$directories = array ();

//     Load::file (APPPATH . 'config' . DIRECTORY_SEPARATOR . 'router.php', true);

//     self::parseRouters ();
//   }
 
//   public static function dir ($prefix, $callback) {
//     $callback ();
//   }
//   private static function getDirs () {
//     if (($dir = array_filter (array_map (function ($trace) {
//                   return isset ($trace['class']) && ($trace['class'] == 'Router') && isset ($trace['function']) && ($trace['function'] == 'dir') && isset ($trace['type']) && ($trace['type'] == '::') && isset ($trace['args'][0]) ? $trace['args'][0] : null;
//                 }, debug_backtrace (DEBUG_BACKTRACE_PROVIDE_OBJECT)))) && ($dir = array_shift ($dir)))
//       return ($t = trim ($dir, '/')) ? is_dir (APPPATH . 'controller' . DIRECTORY_SEPARATOR . str_replace ('/', DIRECTORY_SEPARATOR, $t)) ? explode ('/', $t) : gg ('Router dir 設定錯誤，不存在的目錄：' . $t) : array ();
//     else
//       return array ();
//   }
//   public static function restful ($uris, $controller, $models) {
//     class_exists ('RestfulUrl', false) || Load::sysLib ('RestfulUrl.php', true);

//     is_array ($uris) || $uris = array ($uris);
//     is_array ($models) || $models = array ($models);
//     $c = count ($uris);

//     $t1 = $c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (1, $c - 1))) : '';
//     $t2 = $c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '';

//     $prefixs = implode ('/', array_merge (self::getDirs (), array ($controller)));
//     RestfulURL::addGroup ($prefixs, 'index', self::method ('get', explode ('/', implode ('/(:id)/', $uris)), $controller . '@index(' . $t1 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'show', self::method ('get', explode ('/', implode ('/(:id)/', $uris) . '/(:id)'), $controller . '@show($1' . $t2 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'add', self::method ('get', explode ('/', implode ('/(:id)/', $uris) . '/add'), $controller . '@add(' . $t1 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'create', self::method ('post', explode ('/', implode ('/(:id)/', $uris)), $controller . '@create(' . $t1 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'edit', self::method ('get', explode ('/', implode ('/(:id)/', $uris) . '/(:id)/edit'), $controller . '@edit($1' . $t2 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'update', self::method ('put', explode ('/', implode ('/(:id)/', $uris) . '/(:id)'), $controller . '@update($1' . $t2 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'destroy', self::method ('delete', explode ('/', implode ('/(:id)/', $uris) . '/(:id)'), $controller . '@destroy($1' . $t2 . ')', $models, true));

//     RestfulURL::addGroup ($prefixs, '', self::method ('get', explode ('/', implode ('/(:id)/', $uris) . '/(:id)/(:any)'), $controller . '@$' . ($c + 1) . '($1' . $t2 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, '', self::method ('post', explode ('/', implode ('/(:id)/', $uris) . '/(:id)/(:any)'), $controller . '@$' . ($c + 1) . '($1' . $t2 . ')', $models, true));
    
//     // RestfulURL::addGroup ($prefixs, 'sorts', self::method ('get', explode ('/', implode ('/(:id)/', $uris) . '/sorts'), $controller . '@sorts(' . $t1 . ')', $models, true));
//     RestfulURL::addGroup ($prefixs, 'sorts', self::method ('post', explode ('/', implode ('/(:id)/', $uris) . '/sorts'), $controller . '@sorts(' . $t1 . ')', $models, true));
//   }
//   private static function method ($m, $formats, $uri, $models = array (), $restful = false) {
//     $prefixs = self::getDirs ();
//     $formats = array_filter ($formats, function ($format) { return $format !== ''; });

//     $uri = preg_split ('/[@,\(\)\s]+/', $uri);

//     $controller = array_shift ($uri);
//     $method = array_shift ($uri);
//     $params = array_filter ($uri, function ($param) { return $param !== null && $param !== ''; });
//     $position = array_merge ($prefixs, array ($controller, $method), $params);

//     array_push (self::$routers, array (
//         'method' => $m,
//         // 'ids' => $return = implode ('/', array_merge ($prefixs, $formats)),
//         'cnt' => substr_count ($return = implode ('/', array_merge ($prefixs, $formats)), ':id'),
//         'format' => $return = str_replace (array (':any', ':num', ':id'), array ('[^/]+', '[0-9]+', '[0-9]+'), $return),
//         'position' => implode ('/', $position),
//         'params' => count ($prefixs) + 2,
//         'restful' => $restful,
//         'models' => $models,
//         'group' => array_merge ($prefixs, array ($controller))
//       ));

//     return $return;
//   }

//   public static function __callStatic ($name, $arguments) {
//     in_array ($name == strtolower ($name), array ('get', 'post', 'put', 'delete', 'cli')) || gg ('Router 沒有此「' . $name . '」Method！');
//     return self::method ($name, array (array_shift ($arguments)), array_shift ($arguments), array_shift ($arguments));
//   }

//   private static function parseRouters () {
//     $uri = implode ('/', URL::segments ());

//     $method = strtolower (request_is_cli () ? 'cli' : (isset ($_POST['_method']) ? $_POST['_method'] : (isset ($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get')));

//     foreach (self::$routers as $router)
//       if ($router['method'] == $method && preg_match ('#^' . $router['format'] . '$#', $uri, $matches)) {

//         strpos ($router['position'], '$') !== false && strpos ($router['format'], '(') !== false && $router['position'] = preg_replace ('#^' . $router['format'] . '$#', $router['position'], $uri);

//         $position = explode ('/', $router['position']);

//         self::$router = $router;

//         // self::$routers = self::$router['restful'] ? array_slice ($position, self::$router['params']) : array_slice ($position, 0-self::$router['cnt']);
//         self::$router['params'] = array ();

//         return self::setRequest ($position);
//       }

//     self::setRequest (URL::segments ());
//   }
//   private static function setRequest ($segments) {
//     if (!$segments = self::validateRequest ($segments))
//       return ;

//     self::setClass (array_shift ($segments));
//     self::setMethod (array_shift ($segments));
    
//     self::setParams ($segments);
//   }
//   private static function validateRequest ($segments) {
//     $c = count ($segments = array_values (array_filter ($segments, function ($segment) { return $segment !== null && $segment !== ''; })));

//     while ($c-- > 0)
//       if (($test = self::getDirectory () . str_replace ('-', '_', $segments[0])) && !file_exists (APPPATH . 'controller' . DIRECTORY_SEPARATOR . $test . EXT) && is_dir (APPPATH . 'controller' . DIRECTORY_SEPARATOR . self::getDirectory () . $segments[0]) && self::appendDirectory (array_shift ($segments)))
//         continue;
//       else
//         return $segments;

//     return $segments;
//   }
//   public static function getDirectory () {
//     return implode (DIRECTORY_SEPARATOR, self::$directories) . (self::$directories ? DIRECTORY_SEPARATOR : '');
//   }
//   public static function appendDirectory ($dir) {
//     return array_push (self::$directories, $dir);
//   }
//   public static function setClass ($class) {
//     return self::$class = $class;
//   }
//   public static function setMethod ($method) {
//     return self::$method = $method ? $method : 'index';
//   }
//   public static function setParams ($params) {
//     if (self::$router['models'])
//       foreach (self::$router['models'] as $i => $model)
//         isset ($params[$i]) && array_push (self::$router['params'], array ($model, $params[$i]));
//     unset (self::$router['models']);

//     return self::$params = $params;
//   }
//   public static function getClass () {
//     return self::$class;
//   }
//   public static function getMethod () {
//     return self::$method;
//   }
//   public static function getParams () {
//     return self::$params;
//   }
// }
