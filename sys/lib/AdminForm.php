<?php defined('MAZU') || exit('此檔案不允許讀取！');

abstract class AdminFormRow {
  protected $title, $name, $tip, $need;
  
  public function __construct() {
    $this->title = null;
    $this->name  = null;
    $this->need  = null;
    $this->tip   = null;
  }

  public function title($title) {
    is_string($title) && $this->title = $title;
    return $this;
  }

  public function isNeed($need = true) {
    is_bool($need) && $this->need = $need;
    return $this;
  }

  public function tip($tip) {
    is_string($tip) && $this->tip = $tip;
    return $this;
  }
  
  public function name($name) {
    is_string($name) && $this->name = $name;
    return $this;
  }
  
  public function b() {
    $attrs = [];
    array_push($attrs, $this->need === true ? 'class="need"' : null);
    array_push($attrs, $this->tip !== null ? 'data-tip="' . $this->tip . '"' : null);
    return '<b' . AdminForm::attrs($attrs) . '>' . $this->title . '</b>';
  }

  public function __toString() {
    $this->title !== null || gg('請設定 ' . str_replace('AdminForm', '', get_called_class()) . ' 標題(title)！');
    $this->name  !== null || gg('請設定 ' . str_replace('AdminForm', '', get_called_class()) . ' 名稱(name)！');
    return '';
  }
}

class AdminFormInput extends AdminFormRow {
  private $type, $d4, $placeholder, $autofocus, $minLength, $maxLength;
  const TYPES = ['text', 'color', 'number', 'date', 'email', 'password'];

  public function __construct($type = 'text') {
    parent::__construct();

    $this->type = null;
    $this->d4 = null;
    $this->placeholder = null;
    $this->autofocus = null;
    $this->minLength = null;
    $this->maxLength = null;
    $this->type($type);
  }

  public function type($type) {
    is_string($type) && in_array($type, AdminFormInput::TYPES) && $this->type = $type;
    return $this;
  }
  
  public function d4($d4) {
    is_string($d4) && $this->d4 = $d4;
    return $this;
  }
  
  public function placeholder($placeholder) {
    is_string($placeholder) && $this->placeholder = $placeholder;
    return $this;
  }
  
  public function autofocus($autofocus = true) {
    is_bool($autofocus) && $this->autofocus = $autofocus;
    return $this;
  }
  
  public function minLength($minLength) {
    is_numeric($minLength) && $this->minLength = $minLength;
    return $this;
  }
  
  public function maxLength($maxLength) {
    is_numeric($maxLength) && $this->maxLength = $maxLength;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->type  !== null || gg('請設定 Input 類型(type)！', '只允許以下類型：' . implode(', ', AdminFormInput::TYPES));
    $this->d4    !== null || gg('請設定 Input 預設值(d4)！');
    
    $this->need && ($this->minLength === null || $this->minLength <= 0) && $this->minLength(1);
    $value = AdminForm::$flash[$this->name] !== null ? AdminForm::$flash[$this->name] : $this->d4;
    
    $attrs = [];
    array_push($attrs, 'type="' . $this->type . '"');
    array_push($attrs, 'name="' . $this->name .'"');
    array_push($attrs, 'value="' . $value . '"');
    array_push($attrs, $this->placeholder !== null ? 'placeholder="' . $this->placeholder . '"' : null);
    array_push($attrs, $this->autofocus === true ? 'autofocus' : null);
    array_push($attrs, is_numeric($this->minLength) ? 'minlength="' . $this->minLength . '"' : null);
    array_push($attrs, is_numeric($this->maxLength) ? 'maxlength="' . $this->maxLength . '"' : null);
    array_push($attrs, $this->need === true ? 'required' : null);

    $return = '';
    $return .= '<label class="row">';
      $return .= $this->b();
      $return .= '<input' . AdminForm::attrs($attrs) .'/>';
    $return .= '</label>';

    return $return;
  }
}

class AdminFormTextArea extends AdminFormRow {
  private $d4, $placeholder, $autofocus, $minLength, $maxLength;
  const TYPES = ['pure', 'ckeditor'];

  public function __construct($type = 'text') {
    parent::__construct();
    
    $this->type = null;
    $this->d4 = null;
    $this->placeholder = null;
    $this->autofocus = null;
    $this->minLength = null;
    $this->maxLength = null;

    $this->type($type);
  }

  public function type($type) {
    is_string($type) && in_array($type, AdminFormTextArea::TYPES) && $this->type = $type;
    return $this;
  }

  public function d4($d4) {
    is_string($d4) && $this->d4 = $d4;
    return $this;
  }

  public function placeholder($placeholder) {
    is_string($placeholder) && $this->placeholder = $placeholder;
    return $this;
  }

  public function autofocus($autofocus = true) {
    is_bool($autofocus) && $this->autofocus = $autofocus;
    return $this;
  }

  public function minLength($minLength) {
    is_numeric($minLength) && $this->minLength = $minLength;
    return $this;
  }

  public function maxLength($maxLength) {
    is_numeric($maxLength) && $this->maxLength = $maxLength;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->type  !== null || gg('請設定 TextArea 類型(type)！', '只允許以下類型：' . implode(', ', AdminFormTextArea::TYPES));
    $this->d4    !== null || gg('請設定 TextArea 預設值(d4)！');
    
    $value = AdminForm::$flash[$this->name] !== null ? AdminForm::$flash[$this->name] : $this->d4;
    $this->need && ($this->minLength === null || $this->minLength <= 0) && $this->minLength(1);
    
    $attrs = [];
    array_push($attrs, 'class="' . $this->type . '"');
    array_push($attrs, 'name="' . $this->name .'"');
    array_push($attrs, $this->placeholder !== null ? 'placeholder="' . $this->placeholder . '"' : null);
    array_push($attrs, $this->autofocus === true ? 'autofocus' : null);
    array_push($attrs, is_numeric($this->minLength) ? 'minlength="' . $this->minLength . '"' : null);
    array_push($attrs, is_numeric($this->maxLength) ? 'maxlength="' . $this->maxLength . '"' : null);
    array_push($attrs, $this->need === true ? 'required' : null);

    $return = '';
    $return .= '<label class="row">';
      $return .= $this->b();
      $return .= '<textarea' . AdminForm::attrs($attrs) .'>' . $value . '</textarea>';
    $return .= '</label>';

    return $return;
  }
}

class AdminFormImage extends AdminFormRow {
  private $src, $accept;

  public function __construct() {
    $this->src = null;
    $this->accept = null;
  }

  public function src($src) {
    is_string($src) && $this->src = $src;
    return $this;
  }

  public function accept($accept) {
    is_string($accept) && $this->accept = $accept;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->src   !== null || gg('請設定 Image 圖片網址(src)！');

    $attrs = [];
    array_push($attrs, 'type="file"');
    array_push($attrs, 'name="' . $this->name .'"');
    array_push($attrs, $this->accept !== null ? 'accept="' . $this->accept . '"' : null);

    $return = '';
    $return .= '<label class="row">';
      $return .= $this->b();

      $return .= '<div class="drop-img">';
        $return .= '<img src="' . $this->src . '" />';
        $return .= '<input' . AdminForm::attrs($attrs) .'/>';
      $return .= '</div>';

    $return .= '</label>';

    return $return;
  }
}

class AdminFormImages extends AdminFormRow {
  private $srcs, $accept;

  public function __construct() {
    $this->srcs = null;
    $this->accept = null;
  }

  public function accept($accept) {
    is_string($accept) && $this->accept = $accept;
    return $this;
  }

  public function srcs(array $srcs) {
    is_array($srcs) && $this->srcs = $srcs;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->srcs   !== null || gg('請設定 Images 圖片網址陣列([srcs])！');

    $attrs = [];
    array_push($attrs, 'type="file"');
    array_push($attrs, 'name="' . $this->name .'[]"');
    array_push($attrs, $this->accept !== null ? 'accept="' . $this->accept . '"' : null);

    $return = '';
    $return .= '<div class="row">';
      $return .= $this->b();
      $return .= '<div class="multi-drop-imgs">';

      $return .= implode('', array_map(function($src) use($attrs) {
        $return = '';
        $return .= '<div class="drop-img">';
          $return .= '<img src="' . $src . '" />';
          $return .= '<input' . AdminForm::attrs($attrs) .'/>';
          $return .= '<a class="icon-04"></a>';
        $return .= '</div>';
        return $return;
      }, array_merge([''], $this->srcs)));

      $return .= '</div>';
    $return .= '</div>';

    return $return;
  }
}

class AdminFormSelect extends AdminFormRow {
  private $d4, $autofocus, $items;

  public function __construct() {
    $this->d4 = null;
    $this->autofocus = null;
    $this->items = null;
  }

  public function d4($d4) {
    is_string($d4) && $this->d4 = $d4;
    return $this;
  }

  public function items($items) {
    is_array($items) && $this->items = $items;
    return $this;
  }

  public function autofocus($autofocus = true) {
    is_bool($autofocus) && $this->autofocus = $autofocus;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->d4    !== null || gg('請設定 Select 預設值(d4)！');
    $this->items !== null || gg('請設定 Select 選項(items)！');
    
    $value = AdminForm::$flash[$this->name] !== null ? AdminForm::$flash[$this->name] : $this->d4;

    $attrs = [];
    array_push($attrs, 'name="' . $this->name .'"');
    array_push($attrs, $this->autofocus === true ? 'autofocus' : null);
    array_push($attrs, $this->need === true ? 'required' : null);

    $return = '';
    $return .= '<label class="row">';
      $return .= $this->b();
      $return .= '<select' . AdminForm::attrs($attrs) .'>';
        $return .= '<option value=""' . ($value == '' ? ' selected' : '') . '>請選擇' . $this->title . '</option>';
        $return .= implode('', array_map(function($item) use($value) {
          return '<option value="' . $item['value'] . '"' . ($value == $item['value']  ? ' selected' : '') . '>' . $item['text'] . '</option>';
        }, $this->items));
      $return .= '</select>';
    $return .= '</label>';

    return $return;
  }
}

class AdminFormRadio extends AdminFormRow {
  private $d4, $items;

  public function __construct() {
    $this->d4 = null;
    $this->items = null;
  }

  public function d4($d4) {
    is_string($d4) && $this->d4 = $d4;
    return $this;
  }

  public function items($items) {
    is_array($items) && $this->items = $items;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->items !== null || gg('請設定 Radio 選項(items)！');

    $value = AdminForm::$flash[$this->name] !== null ? AdminForm::$flash[$this->name] : $this->d4;

    $return = '';
    $return .= '<div class="row">';
      $return .= $this->b();
      $return .= '<div class="radios">';
      $return .= implode('', array_map(function($item) use($value) {
        $return = '';
        $return .= '<label>';
          $return .= '<input type="radio" name="' . $this->name . '" value="' . $item['value'] . '"' . ($this->need === true ? ' required' : '') . ($value !== null && $value == $item['value']  ? ' checked' : '') . '/>';
          $return .= '<span></span>';
          $return .= $item['text'];
        $return .= '</label>';
        return $return;
      }, $this->items));
      $return .= '</div>';
    $return .= '</div>';

    return $return;
  }
}

class AdminFormSwitcher extends AdminFormRow {
  private $d4, $on, $off;

  public function __construct() {
    $this->d4 = null;
    $this->on = null;
    $this->off = null;
  }

  public function d4($d4) {
    is_string($d4) && $this->d4 = $d4;
    return $this;
  }

  public function on($on) {
    is_string($on) && $this->on = $on;
    return $this;
  }

  public function off($off) {
    is_string($off) && $this->off = $off;
    return $this;
  }

  public function __toString() {
    parent::__toString();

    $this->on !== null    || gg('請設定 Switcher 啟用值(on)！');
    $this->off !== null   || gg('請設定 Switcher 關閉值(off)！');

    $value = AdminForm::$flash[$this->name] !== null ? AdminForm::$flash[$this->name] : $this->d4;

    $return = '';
    $return .= '<div class="row min">';
      $return .= $this->b();
      $return .= '<div class="switches">';
        $return .= '<label>';
          $return .= '<input type="checkbox" name="' . $this->name . '" value="' . $this->on . '" data-off="' . $this->off . '"' . ($value !== null && $value == $this->on ? ' checked' : '') . '/>';
          $return .= '<span></span>';
        $return .= '</label>';
      $return .= '</div>';
    $return .= '</div>';

    return $return;
  }
}

class AdminFormCheckbox extends AdminFormRow {
  private $d4s, $items;

  public function __construct() {
    $this->items = null;
    $this->d4s = null;
  }

  public function d4s(array $d4s) {
    is_array($d4s) && $this->d4s = $d4s;
    return $this;
  }

  public function items(array $items) {
    is_array($items) && $this->items = $items;
    return $this;
  }

  public static function inArray($var, $arr) {
    foreach ($arr as $val)
      if (($var === 0 ? '0' : $var) == $val)
        return true;
    return false;
  }

  public function __toString() {
    parent::__toString();

    $this->d4s    !== null || gg('請設定 Checkbox 值(d4s)！');
    $this->items  !== null || gg('請設定 Checkbox 選項(items)！');

    $values = AdminForm::$flash !== null ? isset(AdminForm::$flash[$this->name]) ? AdminForm::$flash[$this->name] : [] : $this->d4s;
    is_array($values) || $values = [];

    $return = '';
    $return .= '<div class="row">';
      $return .= $this->b();

      $return .= '<div class="checkboxs">';
      $return .= implode('', array_map(function($item) use($values) {
        $return = '';
        $return .= '<label>';
          $return .= '<input type="checkbox" value="' . $item['value'] . '" name="' . $this->name . '[]"' . (AdminFormCheckbox::inArray($item['value'], $values) ? ' checked' : '') . '/>';
          $return .= '<span></span>';
          $return .= $item['text'];
        $return .= '</label>';
        return $return;
      }, $this->items));
      $return .= '</div>';
    $return .= '</div>';

    return $return;
  }
}

class AdminForm {
  private $backUrl;
  private $actionUrl;
  private $rows;
  private $obj;
  private $hasImage;
  public static $flash;
  
  public function __construct($obj = null) {
    $this->backUrl = [];
    $this->actionUrl = null;
    $this->rows = [];
    $this->obj = $obj;
    $this->hasImage = false;
    self::$flash = null;
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
  
  public function setFlash($flash) {
    self::$flash = $flash;
    return $this;
  }
  
  public function setActionUrl($actionUrl) {
    $this->actionUrl = $actionUrl;
    return $this;
  }

  public function rows(array $rows) {
    if (is_array($rows))
      foreach ($rows as $row)
        array_push($this->rows, $row) && $this->hasImage |= $row instanceof AdminFormImage || $row instanceof AdminFormImages;

    return $this;
  }

  public static function createAdd() {
    return new static();
  }

  public static function createEdit($obj) {
    return new static($obj);
  }

  public static function attrs() {
    $attrs = implode(' ', array_filter(arrayFlatten(func_get_args())));
    return $attrs ? ' ' . $attrs : '';
  }

  public function __toString() {
    $this->actionUrl || gg('請設定 Action 網址！');

    if (!$this->rows)
      return '';
    
    $return = '';
    $return .= '<form class="form" action="' . $this->actionUrl . '" method="post"' . ($this->hasImage ? ' enctype="multipart/form-data"' : '') . '>';
    $return .= $this->obj ? '<input type="hidden" name="_method" value="put" />' : '';

      foreach ($this->rows as $row)
        $return .= $row;

      $return .= '<div class="ctrl">';
        $return .= '<button type="submit">確定</button>';
        $return .= '<button type="reset">取消</button>';
      $return .= '</div>';
    $return .= '</form>';

    return $return;
  }

  public static function input($type = 'text') {
    return new AdminFormInput($type);
  }

  public static function textarea() {
    return new AdminFormTextArea('pure');
  }

  public static function ckeditor() {
    return new AdminFormTextArea('ckeditor');
  }

  public static function image() {
    return new AdminFormImage();
  }

  public static function images() {
    return new AdminFormImages();
  }

  public static function select() {
    return new AdminFormSelect();
  }

  public static function checkbox() {
    return new AdminFormCheckbox();
  }

  public static function radio() {
    return new AdminFormRadio();
  }

  public static function switcher() {
    return new AdminFormSwitcher();
  }
}
