<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Main extends AdminController {
  public function index() {

    return $this->view
                ->setPath('admin/Main/index.php')
                ->with('currentUrl', Url::base('admin'));
  }
}
