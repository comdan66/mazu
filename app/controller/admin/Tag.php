<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends AdminController {
  
  public function __construct() {
    parent::__construct();

    $this->view->with('title', '文章標籤')
               ->with('currentUrl', Url::base('admin/tags'));
  }

  public function index() {
    $list = AdminList::model('\M\Tag');

    $list->search()
         ->input('ID', 'id = ?')
         ->input('名稱', 'name LIKE ?')
         ->setAddUrl(Url::base('admin/tags/add'))
         ->setSortUrl(Url::base('admin/tags/sort'));

    return $this->view->setPath('admin/Tag/index.php')
                      ->with('list', $list);
  }
  
  public function add() {

  }
  
  public function create() {

  }
  
  public function edit() {

  }
  
  public function update() {

  }
  
  public function show() {

  }
  
  public function delete() {

  }

  public function sort() {
    // $validation = function (&$posts) {
    //   Validation::maybe ($posts, 'changes', '狀態', array ())->isArray ()->doArrayValues ()->doArrayMap (function ($t) {
    //     if (!isset ($t['id'], $t['ori'], $t['now']))
    //       return Validation::error ('格式不正確(1)');

    //     if (!$obj = OriAd::find ('one', array ('select' => 'id,sort', 'where' => Where::create ('id = ? AND sort = ?', $t['id'], $t['ori']))))
    //       return Validation::error ('格式不正確(2)');

    //     return array ('obj' => $obj, 'sort' => $t['now']);
    //   })->doArrayFilter ();
    // };

    // $posts = Input::post ();

    // if ($error = Validation::form ($validation, $posts))
    //   return Output::json ($error, 400);

    // $transaction = function ($posts) {
    //   foreach ($posts['changes'] as $value)
    //     $value['obj']->sort = $value['sort'];

    //   foreach ($posts['changes'] as $value)
    //     if (!$value['obj']->save ())
    //       return false;

    //   return true;
    // };

    // if ($error = OriAd::getTransactionError ($transaction, $posts))
    //   return Output::json ($error, 400);

    // return Output::json (array_map (function ($t) { return array ('id' => $t->id, 'sort' => $t->sort);}, array_column ($posts['changes'], 'obj')));
  }
}
