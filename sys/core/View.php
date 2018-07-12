<?php defined('MAZU') || exit('此檔案不允許讀取！');

class View {
  private $path;
  private $vals;
  private $parent;

  public function __construct($path) {
    $this->path = $path;
    $this->vals = [];
    $this->parent = null;
  }

  public function appendTo(View $parent, $key) {
    $this->parent = $parent->with($key, $this);
    return $this;
  }

  public static function create($path) {
    $path = PATH_VIEW . $path;

    is_readable($path) || gg('View 的路徑錯誤！', '路徑：' . $path);

    return new View($path);
  }

  public static function maybe($path = null) {
    $path = PATH_VIEW . $path;
    is_readable($path) || $path = null;
    return new View($path);
  }

  public function setPath($path) {
    $path = PATH_VIEW . $path;
    is_readable($path) || $path = null;
    $this->path = $path;
    
    return $this;
  }

  public function with($key, $val) {
    $this->vals[$key] = $val;
    return $this;
  }

  public function getVals() {
    return array_map(function($t) {
      return $t instanceof View ? $t->get() : $t;
    }, $this->vals);
  }

  public function output() {
    if ($this->parent instanceof View) {
      foreach ($this->getVals() as $key => $val)
        $this->parent->with($key, $val);
      return $this->parent->output();
    } else {
      return $this->get();
    }

    return $this->parent === null ? View::load($this->path, $this->getVals()) : $this->parent->output();
  }

  public function get() {
    return View::load($this->path, $this->getVals(), true);
  }

  private static function load($___path___, $___params___ = [], $___return___ = false) {
    if ($___path___ === null) {
      
      // 將 include output 存起來
      ob_start();
      echo dump($___params___);
      $buffer = ob_get_contents();
      @ob_end_clean();
    } else {
      extract($___params___);
      
      // 將 include output 存起來
      ob_start();
      include $___path___;
      $buffer = ob_get_contents();
      @ob_end_clean();
    }

    if ($___return___)
      return $buffer;
    else
      echo $buffer;
  }
}