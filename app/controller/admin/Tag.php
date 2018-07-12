<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends AdminController {
  
  public function __construct () {
    parent::__construct ();

    $this->view->with ('title', '廣告列表')
               ->with ('current_url', Url::base ('admin/tags'));
  }
  public function index() {
    return $this->view
                ->setPath('admin/Tag/index.php');
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
}
