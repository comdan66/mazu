<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminCrudController extends AdminController {
  protected $obj;
  
  public function __construct() {
    parent::__construct();
    
    Load::sysLib('AdminList.php');
    Load::sysLib('AdminForm.php');
    Load::sysLib('AdminShow.php');

    $this->obj = null;
    $this->view->withReference('obj', $this->obj);
  }
}
