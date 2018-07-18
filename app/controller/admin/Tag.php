<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends AdminCrudController {
  
  public function __construct() {
    parent::__construct();

    if (in_array(Router::methodName(), ['edit', 'update', 'delete', 'show']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Tag::one('id = ?', $id))))
        Url::refreshWithFailureFlash(Url::toRouter('AdminTagIndex'), '找不到資料！');

    $this->view->with('title', '文章標籤')
               ->with('currentUrl', Url::toRouter('AdminTagIndex'));
  }

  public function index() {
    $list = AdminList::model('\M\Tag', ['order' => AdminListOrder::desc('sort')])
                     ->input('ID', 'id = ?')
                     ->input('名稱', 'name LIKE ?')
                     ->setAddUrl(Url::toRouter('AdminTagAdd'))
                     ->setSortUrl(Url::toRouter('AdminTagSort'));

    return $this->view->setPath('admin/Tag/index.php')
                      ->with('list', $list);
  }
  
  public function add() {
    $form = AdminForm::createAdd()
                     ->setFlash($this->flash['params'])
                     ->setActionUrl(Url::toRouter('AdminTagCreate'))
                     ->setBackUrl(Url::toRouter('AdminTagIndex'), '回列表');

    return $this->view->setPath('admin/Tag/add.php')
                      ->with('form', $form);
  }
  
  public function create() {
    $validator = function(&$posts) {
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
    $error && Url::refreshWithFailureFlash(Url::base('admin/tags/add'), $error, $posts);
    
    Url::refreshWithSuccessFlash(Url::base('admin/tags'), '新增成功！');
  }
  
  public function edit() {
    $form = AdminForm::createEdit($this->obj)
                    ->setFlash($this->flash['params'])
                    ->setActionUrl(Url::toRouter('AdminTagUpdate', $this->obj))
                    ->setBackUrl(Url::toRouter('AdminTagIndex'), '回列表');

    return $this->view->setPath('admin/Tag/edit.php')
                      ->with('form', $form);
  }
  
  public function update() {
    $validator = function(&$posts) {
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
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminTagUpdate', $this->obj), $error, $posts);
    
    Url::refreshWithSuccessFlash(Url::toRouter('AdminTagIndex'), '修改成功！');
  }
  
  public function show() {
    $show = AdminShow::create($this->obj)
                     ->setBackUrl(Url::toRouter('AdminTagIndex'), '回列表');

    return $this->view->setPath('admin/Tag/show.php')
                      ->with('show', $show);
  }
  
  public function delete() {
    $error = transaction(function() {
      return $this->obj->delete();
    });

    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminTagIndex'), $error);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminTagIndex'), '刪除成功！');
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

      return true;
    };

    $posts = Input::post();
    
    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);

    return $error ? ['error' => $error] : array_map(function($change) { return ['id' => $change['obj']->id, 'sort' => $change['obj']->sort]; }, $posts['changes']);
  }
}
