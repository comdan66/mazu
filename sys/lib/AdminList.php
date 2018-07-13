<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminListSearch {
  const KEY = '_q';

  protected $where, $searches, $titles, $counter, $list;
  
  public function __construct($list, &$where = null, $searches = [], $titles = [], $counter = 0) {
    $where !== null || $where = Where::create();

    $this->list = $list;
    $this->where = $where;
    $this->searches = $searches;
    $this->counter = $counter;
    $this->titles = $titles;
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

  public function input($title, $sql, $type = 'text') {
    $this->searches[$key = AdminListSearch::KEY . ($this->counter++)] = ['el' => 'input', 'title' => $title, 'sql' => $sql, 'type' => $type];
    return $this->add($key);
  }

  public function select($title, $sql, array $options) {
    $this->searches[$key = AdminListSearch::KEY . ($this->counter++)] = ['el' => 'select', 'title' => $title, 'sql' => $sql, 'options' => $options];
    return $this->add($key);
  }
  
  public function checkboxs($title, $sql, array $items) {
    $this->searches[$key = AdminListSearch::KEY . ($this->counter++)] = ['el' => 'checkboxs', 'title' => $title, 'sql' => $sql, 'items' => $items];
    return $this->add($key);
  }
  
  public function radios($title, $sql, array $items) {
    $this->searches[$key = AdminListSearch::KEY . ($this->counter++)] = ['el' => 'radios', 'title' => $title, 'sql' => $sql, 'items' => $items];
    return $this->add($key);
  }

  public function __toString() {
    $sortKey = '';

    if ($this->list->sortUrl()) {
      $gets = Input::get();

      if (isset($gets[AdminListOrder::KEY]))
        unset($gets[AdminListOrder::KEY]);

      foreach (array_keys($this->searches) as $key)
        if (isset($gets[$key]))
          unset($gets[$key]);
  
      if (isset($gets[AdminListTable::SORT_KEY]) && $gets[AdminListTable::SORT_KEY] === 'true') {
        $ing = false;
        unset($gets[AdminListTable::SORT_KEY]);
      } else {
        $ing = true;
        $gets[AdminListTable::SORT_KEY] = 'true';
      }

      $gets = http_build_query($gets);
      $gets && $gets = '?' . $gets;
      $sortKey = Url::current() . $gets;
    }

    $return = '<form class="search" action="' . Url::current() . '" method="get">';
      $return .= '<div class="info' . ($this->titles ? ' show' : '') . '">';
        $return .= '<a class="icon-13 conditions-btn"></a>';

        $return .= '<span>' . ($this->list->addUrl() ? '<a href="' . $this->list->addUrl() . '" class="icon-07">新增</a>' : '') . ($sortKey ? '<a href="' . $sortKey . '" class="icon-' . ($ing ? '41' : '18') . '">' . ($ing ? '排序' : '完成') . '</a>' : '') . '</span>';
        $return .= '<span>' . ($this->titles ? '您針對' . implode('、', array_map(function($title) {
          return '「' . $title . '」';
        }, $this->titles)) . '搜尋，結果' : '目前全部') . '共有 <b>' . number_format($this->list->total()) . '</b> 筆。' . '</span>';
      $return .= '</div>';
      $return .= $this->conditions();
    $return .= '</form>';
    return $return;
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
            if (!isset($condition['title']))
              return $return;

            $return .= '<label class="row">';
            $return .= '<b>' . $condition['title'] . '搜尋</b>';
            $return .= '<input name="' . $key . '" type="' . (isset($condition['type']) ? $condition['type'] : 'text') . '" placeholder="' . $condition['title'] . '搜尋" value="' . (empty ($condition['value']) ? '' : $condition['value']) . '" />';
            $return .= '</label>';
            break;
          
          case 'select':
            if (!isset($condition['title'], $condition['options']))
              return $return;

            $return .= '<label class="row">';
            $return .= '<b>' . $condition['title'] . '搜尋</b>';
            $return .= '<select name="' . $key . '">';
            $return .= '<option value="">' . $condition['title'] . '搜尋</option>';
            $return .= implode('', array_map(function($option) use($condition) {
              isset($option['value'], $option['text']) || $option = ['text' => $option, 'value' => $option];
              return $option && isset($option['value'], $option['text']) ? '<option value="' . $option['value'] . '"' . (!empty ($condition['value']) && $condition['value'] == $option['value'] ? ' selected' : '') . '>' . $option['text'] . '</option>' : '';
            }, $condition['options']));
            $return .= '</select>';
            $return .= '</label>';
            break;
          
          case 'checkboxs':
            if (!isset($condition['title'], $condition['items']))
              return $return;

            $return .= '<div class="row">';
            $return .= '<b>' . $condition['title'] . '搜尋</b>';
            $return .= '<div class="checkboxs">';
            $return .= implode('', array_map(function($option) use($condition, $key) {
              isset($option['value'], $option['text']) || $option = ['text' => $option, 'value' => $option];
              return $option && isset($option['value'], $option['text']) ? '<label><input type="checkbox" name="' . $key . '[]" value="' . $option['value'] . '"' . (!empty ($condition['value']) && (is_array ($condition['value']) ? in_array ($option['value'], $condition['value']) : $condition['value'] == $option['value']) ? ' checked' : '') . ' /><span></span>' . $option['text'] . '</label>' : '';
            }, $condition['items']));
            $return .= '</div>';
            $return .= '</div>';
            break;

          case 'radios':
            if (!isset($condition['title'], $condition['items']))
              return $return;

            $return .= '<div class="row">';
            $return .= '<b>' . $condition['title'] . '搜尋</b>';
            $return .= '<div class="radios">';
            $return .= implode('', array_map(function($option) use($condition, $key) {
              isset($option['value'], $option['text']) || $option = ['text' => $option, 'value' => $option];
              return $option && isset($option['value'], $option['text']) ? '<label><input type="radio" name="' . $key . '" value="' . $option['value'] . '"' . (!empty ($condition['value']) && $condition['value'] == $option['value'] ? ' checked' : '') . ' /><span></span>' . $option['text'] . '</label>' : '';
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

  public function setAddUrl($addUrl) {
    return $this->list->setAddUrl($addUrl);
  }

  public function setSortUrl($sortUrl) {
    return $this->list->setSortUrl($sortUrl);
  }
}

class AdminList extends AdminListSearch {
  private $model,
          $modelOptions,
          $table,
          $addUrl,
          $sortUrl,
          $pagination,
          $runQuery,
          $total,
          $search;

  public function __construct($model, $modelOptions = [], &$where = null) {
    parent::__construct($this, $where);

    $this->model = $model;
    $this->modelOptions = $modelOptions;
    
    $this->addUrl = '';
    $this->sortUrl = '';
    $this->table = null;
    $this->search = null;
    $this->pagination = [];
    $this->runQuery = false;
    $this->total = 0;
  }

  public function total() {
    return $this->total;
  }

  public function search() {
    $this->query();
    return new AdminListSearch($this, $this->where, $this->searches, $this->titles, $this->counter);
  }

  public function table() {
    $this->query();
    return $this->table->emptyColumn();
  }

  public function pagination() {
    $this->query();
    return !empty($this->pagination['links']) ? '<div class="pagination"><div>' . implode('', $this->pagination['links']) . '</div></div>' : '';
  }

  public function setAddUrl($addUrl) {
    $this->addUrl = $addUrl;
    return $this;
  }

  public function setSortUrl($sortUrl) {
    $this->sortUrl = $sortUrl;
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

    $this->runQuery = true;

    $model = $this->model;

    $this->total = $model::count($this->where);
    $this->pagination = Pagination::info($this->total);

    $this->objs  = $model::all(array_merge([
     'order'  => AdminListOrder::desc('id'),
     'offset' => $this->pagination['offset'],
     'limit'  => $this->pagination['limit'],
     'where'  => $this->where], $this->modelOptions));

    $this->table = new AdminListTable($this, $this->objs);

    return $this;
  }

  public static function model($model, $modelOptions = [], &$where = null) {
    return new static($model, $modelOptions, $where);
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

  // public function setSwitch ($checked, $attrs = []) {
  //   return form_switch ('', '', '', $checked, $attrs);
  // }

  public function thString($sortUrl = '') {
    return '<th' . ($this->width ? ' width="' . $this->width . '"' : '') . '' . ($this->class ? ' class="' . $this->class . '"' : '') . '>' . AdminListOrder::set($this->title, $sortUrl ? '' : $this->sort) . '</th>';
  }

  public function tdString($obj) {
    $td = $this->td;
    
    $text = '';
    is_string($td) && $text = array_key_exists($td, $obj->attrs()) ? $obj->$td : $td;
    is_callable($td) && $text = $td($obj, $this);
    $text instanceof AdminListTableColumn && $text = (string)$text;

    return '<td' . ($this->width ? ' width="' . $this->width . '"' : '') . '' . ($this->class ? ' class="' . $this->class . '"' : '') . '>' . $text . '</td>';
  }

  public static function create($title = '') {
    return new AdminListTableColumn($title);
  }

  public function __toString() {
    return implode('', array_filter(array_map(function($link) {
      $attr = implode(' ', array_map(function($key, $val) { return $key . '="' . $val . '"'; }, array_keys($link), array_values($link)));
      return $attr ? '<a ' . $attr . '></a>' : null;
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

  public function addLink($url, $attrs = []) {
    $attrs['href'] = $url;
    array_push($this->links, $attrs);
    return $this;
  }
}

class AdminListTable {
  const SORT_KEY = '_s';

  private $list, $objs, $columns;
  
  public function __construct($list, $objs = []) {
    $this->list = $list;
    $this->setObjs($objs);
    $this->emptyColumn();
  }

  public function emptyColumn() {
    $this->columns = [];
    return $this;
  }

  public function setObjs($objs) {
    $this->objs = $objs;
    return $this;
  }

  public function prependClomun(AdminListTableColumn $column) {
    array_unshift($this->columns, $column);
    return $this;
  }

  public function appendClomun(AdminListTableColumn $column) {
    array_push($this->columns, $column);
    return $this;
  }

  public function column($thText, $closure = null, $width = null, $sort = null, $class = null) {
    return $this->appendClomun(AdminListTableColumn::create($thText)
                          ->setTd($closure)
                          ->setWidth($width)
                          ->setSort($sort)
                          ->setClass($class));
  }

  public function __toString() {
    $return = '';
    $sortUrl = $this->list->sortUrl();
    Input::get(AdminListTable::SORT_KEY) === 'true' || $sortUrl = '';

    $sortUrl && $this->prependClomun(AdminListTableColumn::create('排序')->setWidth(44)->setClass('center')->setTd('<span class="icon-01 drag"></span>'));

    if ($sortUrl)
      $return .= '<table class="list dragable" data-sorturl="' . $sortUrl . '">';
    else
      $return .= '<table class="list">';

      $return .= '<thead>';
        $return .= '<tr>';
        $return .= implode('', array_map(function($column) use($sortUrl) {
          return $column->thString($sortUrl);
        }, $this->columns));
        $return .= '</tr>';
      $return .= '</thead>';
      $return .= '<tbody>';

      if (!$this->objs)
        $return .= '<tr><td colspan="' . count($this->columns) . '"></td></tr>';
      else
        $return .= implode('', array_map(function($obj) use($sortUrl) {
          return ($sortUrl && isset($obj->id, $obj->sort) ? '<tr data-id="' . $obj->id . '" data-sort="' . $obj->sort . '">' : '<tr>') . implode('', array_map(function($column) use($obj) { 
            $column = clone $column;
            return $column->tdString($obj); }, $this->columns)) . '</tr>';
        }, $this->objs));

      $return .= '</tbody>';
    $return .= '</table>';

    return '<div class="panel">' . $return . '</div>';
  }
}
