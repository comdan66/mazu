<?php defined('MAZU') || exit('此檔案不允許讀取！');

if (!function_exists('asset')) {
  function asset() {
    $args = func_get_args();
    $args = ltrim(preg_replace('/\/+/', '/', implode('/', arrayFlatten($args))), '/');
    
    return url::base($args);
  }
}

class Asset {
  private $version, $list;
  
  public function __construct($version = 0) {
    $this->version = $version;
  }

  public static function create($version = 0) {
    return new Asset($version);
  }

  public function getList($type = null) {
    return isset($this->list[$type]) ? $this->list[$type] : $this->list;
  }

  public function addList($type, $path, $minify = true) {
    is_string($path) || $path = '/' . ltrim(preg_replace('/\/+/', '/', implode('/', arrayFlatten($path))), '/');
    isset($list[$type]) || $list[$type] = [];
    
    preg_match('/^https?:\/\/.*/', $path) && $minify = false;
    $this->list[$type][$path] = $minify;
    return $this;
  }

  public function addJS($path, $minify = true) {
    return $this->addList('js', $path, $minify);
  }

  public function addCSS($path, $minify = true) {
    return $this->addList('css', $path, $minify);
  }

  public static function url($uri) {
    return asset($uri);
  }

  public function renderCSS() {
    $str = '';

    if (empty($this->list['css']))
      return $str;

    foreach ($this->list['css'] as $path => $minify)
      $str .= '<link href="' . asset($path) . '?v=' . $this->version . '" rel="stylesheet" type="text/css" />';

    return $str;
  }

  public function renderJS() {
    $str = '';

    if (empty($this->list['js']))
      return $str;

    foreach ($this->list['js'] as $path => $minify)
      $str .= '<script src="' . asset($path) . '?v=' . $this->version . '" language="javascript" type="text/javascript" ></script>';

    return $str;
  }
}