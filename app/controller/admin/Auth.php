<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Auth extends Controller {
  
  public function __construct() {
    Load::sysLib('Asset.php');
    Load::sysLib('Session.php');
    Load::sysLib('Validator.php');
  }

  public function logout() {
    Session::unsetData('admin');
    return Url::refreshWithSuccessFlash(Url::toRouter('AdminAuthLogin'), '登出成功！');
  }

  public function login() {
    $flash = Session::getFlashData('flash');

    $asset = Asset::create()
                  ->addCSS('/asset/css/icon-login.css')
                  ->addCSS('/asset/css/admin/login.css')
                  ->addJS('/asset/js/res/jquery-1.10.2.min.js')
                  ->addJS('/asset/js/login.js');

    return View::create('admin/Auth/login.php')
               ->with('asset', $asset)
               ->with('flash', $flash);
  }

  public function signin() {
    $validator = function(&$posts, &$admin) {
      isset($posts['account'])  || Validator::error('帳號必填！');
      isset($posts['password']) || Validator::error('密碼必填！');

      $posts['account']  = trim($posts['account']);
      $posts['password'] = trim($posts['password']);

      // $posts['account'] = strip_tags($posts['account']);
      // $posts['password'] = strip_tags($posts['password']);
      
      $posts['account']  || Validator::error('帳號必填！');
      $posts['password'] || Validator::error('密碼必填！');

      mb_strlen($posts['account']) <= 190 || Validator::error('帳號長度錯誤！');
      mb_strlen($posts['password']) <= 190 || Validator::error('密碼長度錯誤！');

      $admin = \M\Admin::one('account = ?', $posts['account']);
      $admin || Validator::error('此帳號不存在！');

      password_verify($posts['password'], $admin->password) || Validator::error('密碼錯誤！');
    };

    $transaction = function($admin) {
      return $admin->save();
    };

    $posts = Input::post();

    $error = '';
    $error || $error = validator($validator, $posts, $admin);
    $error || $error = transaction($transaction, $admin);
    $error && Url::refreshWithFailureFlash(Url::toRouter('AdminAuthLogin'), $error, $posts);

    Session::setData('admin', $admin);
    Url::refreshWithSuccessFlash(Url::toRouter('AdminMainIndex'), '登入成功！');
  }
}
