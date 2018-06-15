<?php

class View {
  private $path;
  private $vals;

  public function __construct($path) {
    $this->path = $path;
    $this->vals = [];
  }

  public static function create($path) {
    $path = PATH_VIEW . $path;

    if (!is_readable($path))
      gg('View 的路徑錯誤！路徑：' . $path);

    return new View($path);
  }

  public static function maybe($path) {
    $path = PATH_VIEW . $path;

    if (!is_readable($path))
      $path = null;

    return new View($path);
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

  public function get() {
    return View::load ($this->path, $this->getVals(), true);
  }

  private static function load($___path___, $___params___ = [], $___return___ = false) {
    if ($___path___ === null) {
      
      // 將 include output 存起來
      ob_start();
      var_dump($___params___);
      $buffer = ob_get_contents();
      @ob_end_clean();
    } else {
      extract($___params___);
      
      // 將 include output 存起來
      ob_start();
      !include $___path___;
      $buffer = ob_get_contents();
      @ob_end_clean();
    }

    if ($___return___)
      return $buffer;
    else
      echo $buffer;
  }
}