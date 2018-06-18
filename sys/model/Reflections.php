<?php

namespace _M;

use ReflectionClass;

class Reflections {
  
  private $reflections = [];
  private static $instance = null;
  
  public static function instance() {
    return self::$instance ? self::$instance : self::$instance = new static();
  }

  public function add($class=null) {
    isset($this->reflections[$class = $this->getClass($class)]) || $this->reflections[$class] = new ReflectionClass($class);
    return $this;
  }

  public function destroy($class) {
    if (isset($this->reflections[$class]))
      $this->reflections[$class] = null;
  }
  
  public function get($class = null) {
    isset($this->reflections[$class = $this->getClass($class)]) || gg('找不到 Class：' . $class);
    return $this->reflections[$class];
  }

  private function getClass($mixed = null) {
    return !is_object($mixed) ? is_null($mixed) ? $this->getCalledClass() : $mixed : get_class($mixed);
  }

  private function getCalledClass() {
    $backtrace = debug_backtrace();
    return get_class($backtrace[2]['object']);
  }
}