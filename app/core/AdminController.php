<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminController extends Controller {
  protected $asset, $view;
  
  public function __construct() {
    Load::sysLib('Asset.php');
    Load::sysLib('Session.php');
    Load::sysLib('Validator.php');
    Load::sysLib('AdminList.php');
    Load::sysLib('Pagination.php');

    if (!\M\Admin::current())
      return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'failure', 'msg' => $error, 'params' => $posts]);

    $this->asset = Asset::create(1)
         ->addCSS('/asset/css/res/jqui-datepick-20180116.css')
         ->addCSS('/asset/css/icon-admin.css')
         ->addCSS('/asset/css/admin/layout.css')
         ->addJS('/asset/js/res/jquery-1.10.2.min.js')
         ->addJS('/asset/js/res/jquery_ui_v1.12.0.js')
         ->addJS('/asset/js/res/jquery_ujs.js')
         ->addJS('/asset/js/res/imgLiquid-min.js')
         ->addJS('/asset/js/res/timeago.js')
         ->addJS('/asset/js/res/jqui-datepick-20180116.js')
         ->addJS('/asset/js/res/oaips-20180115.js')
         ->addJS('/asset/js/res/autosize-3.0.8.js')
         ->addJS('/asset/js/res/OAdropUploadImg-20180115.js')
         ->addJS('/asset/js/res/ckeditor_d2015_05_18/ckeditor.js')
         ->addJS('/asset/js/res/ckeditor_d2015_05_18/adapters/jquery.js')
         ->addJS('/asset/js/res/ckeditor_d2015_05_18/plugins/tabletools/tableresize.js')
         ->addJS('/asset/js/res/ckeditor_d2015_05_18/plugins/dropler/dropler.js')
         ->addJS('/asset/js/admin/layout.js');

    $this->asset->addCSS ('/asset/css/admin/list.css')
                ->addJS ('/asset/js/admin/list.js');

    $flash = Session::getFlashData('flash');

    // get_flash_params ($flash['params']);

    $this->view = View::maybe()
                      ->appendTo(View::create('admin/layout.php'), 'content')
                      ->with('asset', $this->asset)
                      ->with('flash', $flash)
                      ->with('currentUrl', null)
                      ->with('asset', $this->asset);

    Pagination::$firstClass  = 'icon-30';
    Pagination::$prevClass   = 'icon-05';
    Pagination::$activeClass = 'active';
    Pagination::$nextClass   = 'icon-06';
    Pagination::$lastClass   = 'icon-31';
    Pagination::$firstText   = '';
    Pagination::$lastText    = '';
    Pagination::$prevText    = '';
    Pagination::$nextText    = '';
  }
}
