<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Admin extends AdminCrudController {
  private $ignoreIds;
  
  public function __construct() {
    parent::__construct();

    $this->ignoreIds = [1];

    if (in_array(Router::methodName(), ['edit', 'update', 'delete', 'show']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Admin::one('id = ? AND id NOT IN(?)', $id, $this->ignoreIds))))
        Url::refreshWithFailureFlash(Url::toRouter('AdminAdminIndex'), '找不到資料！');

    $this->view->with('title', '後台帳號')
               ->with('currentUrl', Url::toRouter('AdminAdminIndex'));
  }

  public function index() {
    $where = Where::create('id NOT IN(?)', $this->ignoreIds);

    $list = AdminList::model('\M\Admin', ['where' => $where, 'include' => ['roles']])
                     ->input('ID', 'id = ?')
                     ->input('名稱', 'name LIKE ?')
                     ->checkboxs('角色', function ($vals) {
                       $ids = array_unique(arrayColumn(\M\AdminRole::all(['select' => 'adminId', 'where' => ['role IN (?)', $vals]]), 'adminId'));
                       return Where::create('id IN (?)', $ids);
                     }, items(array_keys(\M\AdminRole::ROLE), \M\AdminRole::ROLE))
                     ->setAddUrl(Url::toRouter('AdminAdminAdd'));

    return $this->view->setPath('admin/Admin/index.php')
                      ->with('list', $list);
  }
  
  public function add() {
    $form = AdminForm::createAdd()
                     ->setFlash($this->flash['params'])
                     ->setActionUrl(Url::toRouter('AdminAdminCreate'))
                     ->setBackUrl(Url::toRouter('AdminAdminIndex'), '回列表');

    return $this->view->setPath('admin/Admin/add.php')
                      ->with('form', $form);
  }
  
  public function create() {
    $validator = function(&$posts) {
      // name
      isset($posts['name']) || Validator::error('名稱必填！');
      $posts['name'] = strip_tags(trim($posts['name']));
      $posts['name'] || Validator::error('名稱必填！');
      mb_strlen($posts['name']) <= 190 || Validator::error('名稱長度錯誤！');
      
      // account
      isset($posts['account']) || Validator::error('帳號必填！');
      $posts['account'] = strip_tags(trim($posts['account']));
      $posts['account'] || Validator::error('帳號必填！');
      mb_strlen($posts['account']) <= 190 || Validator::error('帳號長度錯誤！');
      
      // password
      isset($posts['pwd']) || Validator::error('密碼必填！');
      $posts['pwd'] = strip_tags(trim($posts['pwd']));
      $posts['pwd'] || Validator::error('密碼必填！');
      mb_strlen($posts['pwd']) <= 190 || Validator::error('密碼長度錯誤！');
      $posts['password'] = password_hash($posts['pwd'], PASSWORD_DEFAULT);

      // roles
      isset($posts['roles']) || $posts['roles'] = [];
      $posts['roles'] = array_filter($posts['roles'], function($role) { return array_key_exists($role, \M\AdminRole::ROLE); });
      $posts['roles'] || Validator::error('沒有選擇角色！');
    };

    $transaction = function(&$posts) {
      if (!$obj = \M\Admin::create($posts))
        return false;

      // roles
      foreach ($posts['roles'] as $role)
        if (!\M\AdminRole::create(['adminId' => $obj->id, 'role' => $role]))
          return false;
      
      return true;
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminAdminAdd'), $error, $posts);
    
    Url::refreshWithSuccessFlash(Url::toRouter('AdminAdminIndex'), '新增成功！');
  }
  
  public function edit() {
    $form = AdminForm::createEdit($this->obj)
                    ->setFlash($this->flash['params'])
                    ->setActionUrl(Url::toRouter('AdminAdminUpdate', $this->obj))
                    ->setBackUrl(Url::toRouter('AdminAdminIndex'), '回列表');

    return $this->view->setPath('admin/Admin/edit.php')
                      ->with('form', $form);
  }
  
  public function update() {
    $validator = function(&$posts) {
      // name
      isset($posts['name']) || Validator::error('名稱必填！');
      $posts['name'] = strip_tags(trim($posts['name']));
      $posts['name'] || Validator::error('名稱必填！');
      mb_strlen($posts['name']) <= 190 || Validator::error('名稱長度錯誤！');
      
      // account
      isset($posts['account']) || Validator::error('帳號必填！');
      $posts['account'] = strip_tags(trim($posts['account']));
      $posts['account'] || Validator::error('帳號必填！');
      mb_strlen($posts['account']) <= 190 || Validator::error('帳號長度錯誤！');
      
      // password
      isset($posts['pwd']) || Validator::error('密碼必填！');
      $posts['pwd'] = strip_tags(trim($posts['pwd']));
      $posts['pwd'] || Validator::error('密碼必填！');
      mb_strlen($posts['pwd']) <= 190 || Validator::error('密碼長度錯誤！');
      $posts['password'] = password_hash($posts['pwd'], PASSWORD_DEFAULT);

      // roles
      isset($posts['roles']) || $posts['roles'] = [];
      $posts['roles'] = array_filter($posts['roles'], function($role) { return array_key_exists($role, \M\AdminRole::ROLE); });
      $posts['roles'] || Validator::error('沒有選擇角色！');
    };

    $transaction = function(&$posts) {
      if (!($this->obj->columnsUpdate($posts) && $this->obj->save()))
        return false;

      // tags
      $oris = arrayColumn($this->obj->roles, 'role');
      $dels = array_diff($oris, $posts['roles']);
      $adds = array_diff($posts['roles'], $oris);

      foreach ($dels as $del)
        if ($role = \M\AdminRole::one('adminId = ? AND role = ?', $this->obj->id, $del))
          if (!$role->delete())
            return false;

      foreach ($adds as $add)
        if (!\M\AdminRole::create(['adminId' => $this->obj->id, 'role' => $add]))
          return false;

      return true;
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts);
    $error || $error = transaction($transaction, $posts);
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminAdminEdit', $this->obj), $error, $posts);
    
    Url::refreshWithSuccessFlash(Url::toRouter('AdminAdminIndex'), '修改成功！');
  }
  
  public function show() {
    $show = AdminShow::create($this->obj)
                     ->setBackUrl(Url::toRouter('AdminAdminIndex'), '回列表');

    return $this->view->setPath('admin/Admin/show.php')
                      ->with('show', $show);
  }
  
  public function delete() {
    $error = transaction(function() {
      return $this->obj->delete();
    });

    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminAdminIndex'), $error);

    Url::refreshWithSuccessFlash(Url::toRouter('AdminAdminIndex'), '刪除成功！');
  }
}
