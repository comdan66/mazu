<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminShow {
  private $obj;
  private $backUrl;
  private $datas;
  private $string;

  public function __construct(\M\Model $obj) {
    $this->obj = $obj;
    $this->backUrl = [];
    $this->datas = [];
    $this->string = null;
  }

  public function back() {
    $str = '';

    if (!$this->backUrl)
      return $str;

    $str .= '<div class="back">';
    $str .= '<a href="' . $this->backUrl['href'] . '" class="icon-36">' . $this->backUrl['text'] . '</a>';
    $str .= '</div>';
    return $str;
  }
  
  public function setBackUrl($backUrl, $text = '回上一頁') {
    $this->backUrl = ['href' => $backUrl, 'text' => $text];
    return $this;
  }
  
  public static function create(\M\Model $obj) {
    return new static($obj);
  }
  public function __toString() {
    if ($this->string !== null)
      return $this->string;

    $this->string = '';
    $this->string .= implode('', $this->datas);

    return $this->string;
  }

  private function closure($closure) {
    $text = '';
    is_string($closure) && $text = array_key_exists($closure, $this->obj->attrs()) ? $this->obj->$closure : $closure;
    is_callable($closure) && $text = $closure($obj);
    return $text;
  }

// 以下就是可以用的！

  public function text($title, $closure) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel">' . $this->closure($closure) .'</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function texts($title, array $texts) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel texts">' . implode(array_map(function($text) { return '<span>' . $text . '</span>'; }, $texts)) . '</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function links($title, array $links) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel links">' . implode(array_map(function($text) {
      is_string($text) && $text = ['href' => $text, 'text' => $text];
      return '<a class="icon-45" href="' . $text['href'] . '">' . $text['text'] . '</a>';
    }, $links)) . '</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function items($title, array $items) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel items">' . implode(array_map(function($text) { return '<span>' . $text . '</span>'; }, $items)) . '</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function textarea($title, $closure) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel">' . $this->closure($closure) .'</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function ckeditor($title, $closure) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel ckeditor">' . $this->closure($closure) .'</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function image($title, $src) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel images">' . implode('', array_map(function($src) { return '<div class="_ic image"><img src="' . $src . '"><div class="icon-13"></div></div>'; }, [$src])) .'</div>';
    array_push($this->datas, $return);
    return $this;
  }

  public function images($title, array $srcs) {
    $return = '';
    $return .= '<span class="title">' . $title . '</span>';
    $return .= '<div class="panel images">' . implode('', array_map(function($src) { return '<div class="_ic image"><img src="' . $src . '"><div class="icon-13"></div></div>'; }, $srcs)) .'</div>';
    array_push($this->datas, $return);
    return $this;
  }
}