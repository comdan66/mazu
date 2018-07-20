<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminListTableColumn {
  protected $sort, $class, $width, $title, $td, $links = [];
  
  public function __construct($title) {
    $this->setTitle($title)
         ->setSort('');
  }

  public function setTitle($title) {
    if (is_string($title))
      $this->title = $title;

    return $this;
  }

  public function setWidth($width) {
    if (is_numeric($width))
      $this->width = $width;

    return $this;
  }

  public function setSort($sort) {
    if (is_string($sort))
      $this->sort = $sort;

    return $this;
  }

  public function setTd($td) {
    if (is_callable($td) || is_string($td))
      $this->td = $td;

    return $this;
  }

  public function setClass($class) {
    if (is_string($class))
      $this->class = $class;

    return $this;
  }

  public function thString($sortUrl = '') {
    return '<th' . ($this->width ? ' width="' . $this->width . '"' : '') . '' . ($this->class ? ' class="' . $this->class . '"' : '') . '>' . AdminListOrder::set($this->title, $sortUrl ? '' : $this->sort) . '</th>';
  }

  public function setObj($obj) {
    $this->obj = $obj;
    return $this;
  }

  public function tdString() {
    $td = $this->td;
    
    $td instanceof AdminListTableColumn && $td = (string)$td;
    is_callable($td) && $td = $td($this->obj, $this);
    is_string($td) && $td = array_key_exists($td, $this->obj->attrs()) ? $this->obj->$td : $td;
    is_array($td) && $td = implode('', $td);

    return '<td' . ($this->width ? ' width="' . $this->width . '"' : '') . '' . ($this->class ? ' class="' . $this->class . '"' : '') . '>' . $td . '</td>';
  }

  public static function create($title = '') {
    return new AdminListTableColumn($title);
  }

  public function __toString() {
    return implode('', array_filter(array_map(function($link) {
      $attr = $link[1];
      $attr = implode(' ', array_map(function($key, $val) { return $key . '="' . $val . '"'; }, array_keys($attr), array_values($attr)));
      return $attr ? '<a ' . $attr . '>' .  $link[0] . '</a>' : null;
    }, $this->links)));
  }

  public function addEditLink($url) {
    return $this->addLink($url, ['class' =>'icon-03']);
  }

  public function addDeleteLink($url) {
    return $this->addLink($url, ['class' =>'icon-04', 'data-method' =>'delete']);
  }

  public function addShowLink($url) {
    return $this->addLink($url, ['class' =>'icon-29']);
  }

  public function addLink($url, $attrs = [], $text = '') {
    $attrs['href'] = $url;
    array_unshift($this->links, [$text, $attrs]);
    return $this;
  }

  public function setSwitcher($options = []) {
    if (!(isset($options['column'], $options['on'], $options['off'], $options['url']) && array_key_exists($options['column'], $this->obj->attrs())))
      return '';

    $attr = isset($options['url'], $options['column'], $options['on'], $options['off']) ? 'class="switch ajax" data-url="' . $options['url'] . '" data-column="' . $options['column'] . '" data-true="' . $options['on'] . '" data-false="' . $options['off'] . '"' . (!empty($options['cntlabel']) ? ' data-cntlabel="' . $options['cntlabel'] . '"' : '') : 'class="switch"';

    $return = '';
    $return .= '<label ' . $attr . '>';
      $return .= '<input type="checkbox"' . ($this->obj->$options['column'] == $options['on'] ? ' checked' : '') . '/>';
      $return .= '<span></span>';
    $return .= '</label>';

    return $return;
  }
}

class AdminListOrder {
  const KEY = '_o';
  const SPLIT_KEY = ':';

  private $sort = 'id DESC';

  public function __construct($sort = '') {
    if ($sort && count($sort = array_values(array_filter(array_map('trim', explode(' ', $sort))))) == 2 && in_array(strtolower($sort[1]), ['desc', 'asc']))
      $this->sort = $sort[0] . ' ' . strtoupper($sort[1]);

    if (($sort = Input::get(AdminListOrder::KEY)) && count($sort = array_values(array_filter(array_map('trim', explode(AdminListOrder::SPLIT_KEY, $sort))))) == 2 && in_array(strtolower($sort[1]), ['desc', 'asc']))
      $this->sort = $sort[0] . ' ' . strtoupper($sort[1]);
  }
  
  public static function set($title, $column = '') {
    if (!$column) return $title;

    $gets = Input::get();
    
    if (!(isset($gets[AdminListOrder::KEY]) && count($sort = array_values(array_filter(explode(AdminListOrder::SPLIT_KEY, $gets[AdminListOrder::KEY])))) == 2 && in_array(strtolower($sort[1]), ['desc', 'asc']) && ($sort[0] == $column))) {
      $gets[AdminListOrder::KEY] = $column . AdminListOrder::SPLIT_KEY . 'desc';
      return $title . ' <a href="' . Url::current() . '?' . http_build_query($gets) . '" class="sort"></a>';
    }

    $class = strtolower($sort[1]);

    if ($class != 'asc')
      $gets[AdminListOrder::KEY] = $column . AdminListOrder::SPLIT_KEY . 'asc';
    else
      unset($gets[AdminListOrder::KEY]);

    return $title . ' <a href="' . Url::current() . ($gets ? '?' : '') . http_build_query($gets) . '" class="sort ' . $class . '"></a>';
  }

  private static function _desc($column = '') {
    return ($column ? $column : 'id') . ' ' . strtoupper('desc');
  }

  private static function _asc($column = '') {
    return ($column ? $column : 'id') . ' ' . strtoupper('asc');
  }

  public function __call($name, $arguments) {
    switch (strtolower(trim($name))) {
      case 'asc':
        $this->sort = call_user_func_array(['self', '_asc'], $arguments);
        break;

      case 'desc':
        $this->sort = call_user_func_array(['self', '_desc'], $arguments);
        break;

      default:
        gg('AdminListOrder 沒有「' . $name . '」方法。');
        break;
    }
    return $this;
  }

  public static function __callStatic($name, $arguments) {
    switch (strtolower(trim($name))) {
      case 'asc':
        return AdminListOrder::create(call_user_func_array(['self', '_asc'], $arguments));
        break;

      case 'desc':
        return AdminListOrder::create(call_user_func_array(['self', '_desc'], $arguments));
        break;

      default:
        gg('AdminListOrder 沒有「' . $name . '」方法。');
        break;
    }
  }

  public function __toString() {
    return $this->sort;
  }

  public static function create($sort = '') {
    return new AdminListOrder($sort);
  }
}

class AdminList {
  const SORT_KEY = '_s';
  const SEARCH_KEY = '_q';

  private $model, $modelOptions, $addUrl, $sortUrl, $where, $searches, $titles, $counter, $runQuery, $total, $objs, $pages, $columns, $search, $string;

  public function __construct($model, $modelOptions = []) {
    $this->model = $model;
    $this->modelOptions = $modelOptions;
    
    $this->addUrl = '';
    $this->sortUrl = '';
    $this->setWhere(isset($this->modelOptions['where']) ? $this->modelOptions['where'] : Where::create());

    $this->searches = [];
    $this->titles = [];
    $this->counter = 0;
    
    $this->runQuery = false;
    $this->total = 0;
    $this->objs = [];
    $this->pages = null;
    $this->columns = [];
    
    $this->search = null;
    $this->string = null;
  }

  public function &objs() {
    return $this->query()->objs;
  }
  public function setAddUrl($addUrl) {
    $this->addUrl = $addUrl;
    return $this;
  }

  public function setSortUrl($sortUrl) {
    $this->sortUrl = $sortUrl;
    return $this;
  }

  public function setWhere($where) {
    $this->where = $where;
    return $this;
  }

  public function addUrl() {
    return $this->addUrl;
  }

  public function sortUrl() {
    return $this->sortUrl;
  }

  private function query() {
    if($this->runQuery)
      return $this;

    Load::sysLib('Pagination.php');
    Pagination::$firstClass  = 'icon-30';
    Pagination::$prevClass   = 'icon-05';
    Pagination::$activeClass = 'active';
    Pagination::$nextClass   = 'icon-06';
    Pagination::$lastClass   = 'icon-31';
    Pagination::$firstText   = '';
    Pagination::$lastText    = '';
    Pagination::$prevText    = '';
    Pagination::$nextText    = '';

    $this->runQuery = true;

    $model = $this->model;
    $this->total = $model::count($this->where);
    $this->pages = Pagination::info($this->total);

    $this->objs  = $model::all(array_merge([
     'order'  => AdminListOrder::desc('id'),
     'offset' => $this->pages['offset'],
     'limit'  => $this->pages['limit'],
     'where'  => $this->where], $this->modelOptions));

    $this->pages = '<div class="pagination"><div>' . implode('', $this->pages['links']) . '</div></div>';

    return $this;
  }

  public static function model($model, $modelOptions = []) {
    $modelOptions instanceof Where && $modelOptions = ['where' => $modelOptions];
    return new static($model, $modelOptions);
  }

  private function add($key) {
    $value = Input::get($key, true);

    if ($value === null || $value === '' || (is_array($value) && !count($value)) || empty($this->searches[$key]['sql']))
      return $this;

    is_callable($this->searches[$key]['sql']) && $this->where->and($this->searches[$key]['sql']($value));
    is_string($this->searches[$key]['sql'])   && $this->where->and($this->searches[$key]['sql'], strpos(strtolower($this->searches[$key]['sql']), ' like ') !== false ? '%' . $value . '%' : $value);
    is_object($this->searches[$key]['sql'])   && $this->searches[$key]['sql'] instanceof Where && $this->where->and($this->searches[$key]['sql']);

    $this->searches[$key]['value'] = $value;
    array_push($this->titles, $this->searches[$key]['title']);
    return $this;
  }
  
  private function conditions() {
    $gets = Input::get();

    $return = '<div class="conditions">';
      
      $return .= implode('', array_map(function($key, $condition) use(&$gets) {
        unset($gets[$key]);
        $return = '';

        if (!(isset($condition['el']) && in_array($condition['el'], ['input', 'select', 'checkboxs', 'radios'])))
          return $return;

        switch ($condition['el']) {
          case 'input':
            if (!(isset($condition['title']) && $condition['title']))
              return $return;

            $return .= '<label class="row">';
            $return .= '<b>' . $condition['title'] . '</b>';
            $return .= '<input name="' . $key . '" type="' . (isset($condition['type']) ? $condition['type'] : 'text') . '" placeholder="' . $condition['title'] . '" value="' . (empty ($condition['value']) ? '' : $condition['value']) . '" />';
            $return .= '</label>';
            break;
          
          case 'select':
            if (!(isset($condition['title'], $condition['items']) && $condition['title'] && ($condition['items'] = array_filter($condition['items'], function($item) { return isset($item['text'], $item['value']); }))))
              return $return;

            $return .= '<label class="row">';
            $return .= '<b>' . $condition['title'] . '</b>';
            $return .= '<select name="' . $key . '">';
            $return .= '<option value="">' . $condition['title'] . '</option>';
            $return .= implode('', array_map(function($item) use($condition) {
              return $item && isset($item['value'], $item['text']) ? '<option value="' . $item['value'] . '"' . (!empty ($condition['value']) && $condition['value'] == $item['value'] ? ' selected' : '') . '>' . $item['text'] . '</option>' : '';
            }, $condition['items']));
            $return .= '</select>';
            $return .= '</label>';
            break;
          
          case 'checkboxs':
            if (!(isset($condition['title'], $condition['items']) && $condition['title'] && ($condition['items'] = array_filter($condition['items'], function($item) { return isset($item['text'], $item['value']); }))))
              return $return;

            $return .= '<div class="row">';
            $return .= '<b>' . $condition['title'] . '</b>';
            $return .= '<div class="checkboxs">';
            $return .= implode('', array_map(function($item) use($condition, $key) {
              return $item && isset($item['value'], $item['text']) ? '<label><input type="checkbox" name="' . $key . '[]" value="' . $item['value'] . '"' . (!empty ($condition['value']) && (is_array ($condition['value']) ? in_array ($item['value'], $condition['value']) : $condition['value'] == $item['value']) ? ' checked' : '') . ' /><span></span>' . $item['text'] . '</label>' : '';
            }, $condition['items']));
            $return .= '</div>';
            $return .= '</div>';
            break;

          case 'radios':
            if (!(isset($condition['title'], $condition['items']) && $condition['title'] && ($condition['items'] = array_filter($condition['items'], function($item) { return isset($item['text'], $item['value']); }))))
              return $return;

            $return .= '<div class="row">';
            $return .= '<b>' . $condition['title'] . '</b>';
            $return .= '<div class="radios">';
            $return .= implode('', array_map(function($item) use($condition, $key) {
              return $item && isset($item['value'], $item['text']) ? '<label><input type="radio" name="' . $key . '" value="' . $item['value'] . '"' . (!empty ($condition['value']) && $condition['value'] == $item['value'] ? ' checked' : '') . ' /><span></span>' . $item['text'] . '</label>' : '';
            }, $condition['items']));
            $return .= '</div>';
            $return .= '</div>';
            break;
          
          default:
            return $return;
            break;
        }

        return $return;
      }, array_keys($this->searches), array_values($this->searches)));

      $gets = http_build_query($gets);
      $gets && $gets = '?' . $gets;

      $return .= '<div class="btns">';
        $return .= '<button type="submit">搜尋</button>';
        $return .= '<a href="' . Url::current() . $gets . '">取消</a>';
      $return .= '</div>';
    $return .= '</div>';

    return $return;
  }

  public function search() {
    if ($this->search !== null)
      return $this->search;


    $this->query();

    $sortKey = '';

    if ($this->sortUrl()) {
      $gets = Input::get();

      if (isset($gets[AdminListOrder::KEY]))
        unset($gets[AdminListOrder::KEY]);

      foreach (array_keys($this->searches) as $key)
        if (isset($gets[$key]))
          unset($gets[$key]);
  
      if (isset($gets[AdminList::SORT_KEY]) && $gets[AdminList::SORT_KEY] === 'true') {
        $ing = false;
        unset($gets[AdminList::SORT_KEY]);
      } else {
        $ing = true;
        $gets[AdminList::SORT_KEY] = 'true';
      }

      $gets = http_build_query($gets);
      $gets && $gets = '?' . $gets;
      $sortKey = Url::current() . $gets;
    }

    $this->search = '';

    $this->search .= '<form class="search" action="' . Url::current() . '" method="get">';
      $this->search .= '<div class="info' . ($this->titles ? ' show' : '') . '">';
        $this->search .= '<a class="icon-13 conditions-btn"></a>';

        $this->search .= '<span>' . ($this->addUrl() ? '<a href="' . $this->addUrl() . '" class="icon-07">新增</a>' : '') . ($sortKey ? '<a href="' . $sortKey . '" class="icon-' . ($ing ? '41' : '18') . '">' . ($ing ? '排序' : '完成') . '</a>' : '') . '</span>';
        $this->search .= '<span>' . ($this->titles ? '您針對' . implode('、', array_map(function($title) {
          return '「' . $title . '」';
        }, $this->titles)) . '搜尋，結果' : '目前全部') . '共有 <b>' . number_format($this->total) . '</b> 筆。' . '</span>';
      $this->search .= '</div>';
      $this->search .= $this->conditions();
    $this->search .= '</form>';
    return $this->search;
  }

  public function __toString() {
    if ($this->string !== null)
      return $this->string;

    $this->query();

    $sortUrl = $this->sortUrl();
    Input::get(AdminList::SORT_KEY) === 'true' || $sortUrl = '';

    $sortUrl && $this->prependClomun(AdminListTableColumn::create('排序')->setWidth(44)->setClass('center')->setTd('<span class="icon-01 drag"></span>'));

    $this->string = '';

    $this->string .= '<div class="panel">';
      $this->string .= $sortUrl ? '<table class="list dragable" data-sorturl="' . $sortUrl . '">' : '<table class="list">';

        $this->string .= '<thead>';
          $this->string .= '<tr>';
          $this->string .= implode('', array_map(function($column) use($sortUrl) { return $column->thString($sortUrl); }, $this->columns));
          $this->string .= '</tr>';
        $this->string .= '</thead>';
        $this->string .= '<tbody>';

          $this->string .= $this->objs ? implode('', array_map(function($obj) use($sortUrl) {
            return ($sortUrl && isset($obj->id, $obj->sort) ? '<tr data-id="' . $obj->id . '" data-sort="' . $obj->sort . '">' : '<tr>') . implode('', array_map(function($column) use($obj) { 
              $column = clone $column;
              return $column->setObj($obj)->tdString();
            }, $this->columns)) . '</tr>';
          }, $this->objs)) : '<tr><td colspan="' . count($this->columns) . '"></td></tr>';

        $this->string .= '</tbody>';
      $this->string .= '</table>';
    $this->string .= '</div>';

    return $this->string;
  }

  public function pages() {
    $this->pages !== null || $this->query();
    return $this->pages;
  }

  public function prependClomun(AdminListTableColumn $column) {
    array_unshift($this->columns, $column);
    return $this;
  }

  public function appendClomun(AdminListTableColumn $column) {
    array_push($this->columns, $column);
    return $this;
  }

  public function column($thText, $closure, $width = null, $sort = null, $class = null) {
    return $this->appendClomun(AdminListTableColumn::create($thText)
                          ->setTd($closure)
                          ->setWidth($width)
                          ->setSort($sort)
                          ->setClass($class));
  }
  
// 以下就是可以用的！

  public function input($title, $sql, $type = 'text') {
    $this->searches[$key = AdminList::SEARCH_KEY . ($this->counter++)] = ['el' => 'input', 'title' => $title, 'sql' => $sql, 'type' => $type];
    return $this->add($key);
  }

  public function select($title, $sql, array $items) {
    $this->searches[$key = AdminList::SEARCH_KEY . ($this->counter++)] = ['el' => 'select', 'title' => $title, 'sql' => $sql, 'items' => $items];
    return $this->add($key);
  }
  
  public function checkboxs($title, $sql, array $items) {
    $this->searches[$key = AdminList::SEARCH_KEY . ($this->counter++)] = ['el' => 'checkboxs', 'title' => $title, 'sql' => $sql, 'items' => $items];
    return $this->add($key);
  }
  
  public function radios($title, $sql, array $items) {
    $this->searches[$key = AdminList::SEARCH_KEY . ($this->counter++)] = ['el' => 'radios', 'title' => $title, 'sql' => $sql, 'items' => $items];
    return $this->add($key);
  }
}
