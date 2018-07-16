<?php defined('MAZU') || exit('此檔案不允許讀取！');

class AdminController extends Controller {
  protected $asset, $view, $flash;
  
  public function __construct() {
    parent::__construct();

    Load::sysLib('Asset.php');
    Load::sysLib('Session.php');
    Load::sysLib('Validator.php');

    if (!\M\Admin::current())
      return Url::refreshWithFlash(Url::base('admin', 'login'), ['msg' => '請先登入！', 'params' => []]);

    $this->asset = Asset::create(1)
         ->addCSS('/asset/css/res/jqui-datepick-20180116.css')
         ->addCSS('/asset/css/icon-admin.css')
         ->addCSS('/asset/css/admin/layout.css')
         ->addCSS ('/asset/css/admin/list.css')
         ->addCSS ('/asset/css/admin/form.css')
         ->addCSS ('/asset/css/admin/show.css')

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

    $this->flash = Session::getFlashData('flash');
    !isset($this->flash['params']) || $this->flash['params'] || $this->flash['params'] = null;

    $this->view = View::maybe()
                      ->appendTo(View::create('admin/layout.php'), 'content')
                      ->with('asset', $this->asset)
                      ->with('flash', $this->flash)
                      ->with('currentUrl', null)
                      ->with('asset', $this->asset);
  }
}
