<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Backup extends AdminCrudController {
  
  public function __construct() {
    parent::__construct();

    if (in_array(Router::methodName(), ['edit', 'show', 'unwatch']))
      if (!(($id = Router::params('id')) && ($this->obj = \M\Backup::one('id = ?', $id))))
        Url::refreshWithFailureFlash(Url::toRouter('AdminBackupIndex'), '找不到資料！');

    $this->view->with('title', '每日備份')
               ->with('currentUrl', Url::toRouter('AdminBackupIndex'));
  }

  public function index() {
    $list = AdminList::model('\M\Backup')
                     ->input('ID', 'id = ?')
                     ->input('日期', 'DATE(createAt) = ?', 'date')
                     ->input('大於(Byte)', 'size >= ?', 'number')
                     ->checkboxs('類型', 'type IN (?)', items(array_keys(\M\Backup::TYPE), \M\Backup::TYPE))
                     ->checkboxs('狀態', 'status IN (?)', items(array_keys(\M\Backup::STATUS), \M\Backup::STATUS))
                     ->checkboxs('已讀', 'unwatch IN (?)', items(array_keys(\M\Backup::UNWATCH), \M\Backup::UNWATCH))
                     ;

    return $this->view->setPath('admin/Backup/index.php')
                      ->with('list', $list);
  }
  
  public function show() {
    $show = AdminShow::create($this->obj)
                     ->setBackUrl(Url::toRouter('AdminBackupIndex'), '回列表');

    return $this->view->setPath('admin/Backup/show.php')
                      ->with('show', $show);
  }

  public function unwatch() {
    $validator = function(&$posts) {
      isset($posts['unwatch']) || Validator::error('已讀必填！');
      $posts['unwatch'] = strip_tags(trim($posts['unwatch']));
      $posts['unwatch'] || Validator::error('已讀必填！');
      mb_strlen($posts['unwatch']) <= 190 || Validator::error('已讀長度錯誤！');
      array_key_exists($posts['unwatch'], \M\Article::ENABLE) || Validator::error('已讀錯誤！');
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
}