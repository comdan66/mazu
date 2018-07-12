<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminForm {
  private $backUrl;
  
  public function __construct() {
    $this->backUrl = [];
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

  public static function create() {
    return new static();
  } 
}
