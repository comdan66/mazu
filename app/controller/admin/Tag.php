<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends AdminCrudController {
  
  public function __construct() {
    parent::__construct();

    if (in_array(Router::methodName(), ['edit', 'update', 'delete', 'show', 'enable']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Tag::one('id = ?', $id))))
        Url::refreshWithFlash(Url::base('admin/tags'), ['msg' => '找不到資料。']);

    $this->view->with('title', '文章標籤')
               ->with('currentUrl', Url::base('admin/tags'));
  }

  public function index() {
    $list = AdminList::model('\M\Tag', ['order' => AdminListOrder::desc('sort')])
              ->input('ID', 'id = ?')
              ->input('名稱', 'name LIKE ?')
              ->checkboxs('狀態', 'enable IN (?)', items(array_keys(\M\Tag::ENABLE), \M\Tag::ENABLE))
              ->setAddUrl(Url::base('admin/tags/add'))
              ->setSortUrl(Url::base('admin/tags/sort'));

    return $this->view->setPath('admin/Tag/index.php')
                      ->with('list', $list);
  }
  
  public function add() {
    $form = AdminForm::createAdd()
            ->setFlash($this->flash['params'])
            ->setActionUrl(Url::base('admin/tags'))
            ->setBackUrl(Url::base('admin/tags/'), '回列表');

    return $this->view->setPath('admin/Tag/add.php')
                      ->with('form', $form);
  }
  
  public function create() {
    $validator = function(&$posts) {
      // enable
      isset($posts['enable']) || Validator::error('狀態不存在！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態不存在！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Tag::ENABLE) || Validator::error('狀態錯誤！');

      // name
      isset($posts['name']) || Validator::error('名稱不存在！');
      $posts['name'] = strip_tags(trim($posts['name']));
      $posts['name'] || Validator::error('名稱不存在！');
      mb_strlen($posts['name']) <= 190 || Validator::error('名稱長度錯誤！');

      // sort
      $posts['sort'] = \M\Tag::count();
    };

    $transaction = function(&$posts) {
      return \M\Tag::create($posts);
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);
    $error && Url::refreshWithFlash(Url::base('admin/tags/add'), ['msg' => $error, 'params' => $posts]);
    Url::refreshWithFlash(Url::base('admin/tags'), '新增成功！');
  }
  
  public function edit($id) {
    $form = AdminForm::createEdit($this->obj)
            ->setFlash($this->flash['params'])
            ->setActionUrl(Url::base('admin/tags/' . $this->obj->id))
            ->setBackUrl(Url::base('admin/tags/'), '回列表');

    return $this->view->setPath('admin/Tag/edit.php')
                      ->with('obj', $this->obj)
                      ->with('form', $form);
  }
  
  public function update($id) {
    $validator = function(&$posts) {
      // enable
      isset($posts['enable']) || Validator::error('狀態不存在！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態不存在！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Tag::ENABLE) || Validator::error('狀態錯誤！');

      // name
      isset($posts['name']) || Validator::error('名稱不存在！');
      $posts['name'] = strip_tags(trim($posts['name']));
      $posts['name'] || Validator::error('名稱不存在！');
      mb_strlen($posts['name']) <= 190 || Validator::error('名稱長度錯誤！');
    };

    $transaction = function(&$posts) {
      return $this->obj->columnsUpdate($posts) && $this->obj->save();
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);
    $error && Url::refreshWithFlash(Url::base('admin/tags/' . $this->obj->id . '/edit'), ['msg' => $error, 'params' => $posts]);
    Url::refreshWithFlash(Url::base('admin/tags'), '修改成功！');
  }
  
  public function show($id) {
    $show = AdminShow::create($this->obj)
                     ->setBackUrl(Url::base('admin/tags/'), '回列表');

    return $this->view->setPath('admin/Tag/show.php')
                      ->with('show', $show);
  }
  
  public function delete($id) {
    $error = transaction(function() {
      return $this->obj->delete();
    });
    Url::refreshWithFlash(Url::base('admin/tags/'), $error ? ['msg' => $error] : '刪除成功！');
  }

  public function enable($obj) {
    $validator = function(&$posts) {
      isset($posts['enable']) || Validator::error('狀態不存在！');
      $posts['enable'] = strip_tags(trim($posts['enable']));
      $posts['enable'] || Validator::error('狀態不存在！');
      mb_strlen($posts['enable']) <= 190 || Validator::error('狀態長度錯誤！');
      array_key_exists($posts['enable'], \M\Tag::ENABLE) || Validator::error('狀態錯誤！');
    };

    $transaction = function(&$posts) {
      return $this->obj->columnsUpdate($posts) && $this->obj->save();
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);
    return $error ? ['error' => $error] : $posts;
  }

  public function sort() {
    $validator = function(&$posts) {
      $posts['changes'] = array_filter(array_map(function($change) {
        if (!isset($change['id'], $change['ori'], $change['now']))
          return null;

        if (!$obj = \M\Tag::one(['select' => 'id,sort', 'where' => ['id = ? AND sort = ?', $change['id'], $change['ori']]]))
          return null;

        return ['obj' => $obj, 'sort' => $change['now']];
      }, isset($posts['changes']) ? $posts['changes'] : []));
    };

    $transaction = function(&$posts) {
      foreach ($posts['changes'] as $change)
        $change['obj']->sort = $change['sort'];

      foreach ($posts['changes'] as $change)
        if (!$change['obj']->save())
          return false;

      $posts = array_map(function($change) {
        return ['id' => $change['obj']->id, 'sort' => $change['obj']->sort];
      }, $posts['changes']);

      return true;
    };

    $posts = Input::post();
    
    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);
    return $error ? ['error' => $error] : $posts;
  }
}
