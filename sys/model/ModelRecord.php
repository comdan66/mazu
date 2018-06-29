<?php

namespace _M;

class ModelRecord {
  private static $caches = [];
  public $dirtyAttrs = [];
  public $hash = null;

  public static function instance($hash) {
    return isset(self::$caches[$hash]) ? self::$caches[$hash] : self::$caches[$hash] = new ModelRecord($hash);
  }
  protected function __construct($hash) {
    $this->hash = $hash;
    $this->dirtyAttrs = [];
    echo "string";
  }

  // public function flagDirty($name = null) {
  //   $this->dirtyAttrs || $this->cleanFlagDirty();
  //   $this->dirtyAttrs[$name] = true;
  //   return $this;
  // }
}

// ModelRecord::instance($this->hash)->dirtyAttrs