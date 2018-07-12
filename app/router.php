<?php defined('MAZU') || exit('此檔案不允許讀取！');

Router::get('/admin/logout')->work(function() {
  Load::sysLib('Session.php');
  Session::unsetData('token');
  return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'success', 'msg' => '登出成功！', 'params' => []]);
});

Router::get('/admin/login')->work(function() {
  Load::sysLib('Asset.php');
  Load::sysLib('Session.php');

  $flash = Session::getFlashData('flash');

  $asset = Asset::create()
                ->addCSS('/asset/css/icon-login.css')
                ->addCSS('/asset/css/admin/login.css')
                ->addJS('/asset/js/res/jquery-1.10.2.min.js')
                ->addJS('/asset/js/login.js');

  return View::create('admin/auth/login.php')
             ->with('asset', $asset)
             ->with('flash', $flash);
});

Router::post('/admin/login')->work(function() {
  Load::sysLib('Validator.php');
  Load::sysLib('Session.php');

  $validator = function(&$posts, &$admin) {
    isset($posts['account'])  || Validator::error('帳號不存在！');
    isset($posts['password']) || Validator::error('密碼不存在！');

    $posts['account']  = trim($posts['account']);
    $posts['password'] = trim($posts['password']);

    $posts['account']  || Validator::error('帳號不存在！');
    $posts['password'] || Validator::error('密碼不存在！');

    $posts['account'] = strip_tags($posts['account']);
    $posts['password'] = strip_tags($posts['password']);

    $admin = \M\Admin::one('account = ?', $posts['account']);
    $admin || Validator::error('此帳號不存在！');

    password_verify($posts['password'], $admin->password) || Validator::error('密碼錯誤！');
  };

  $transaction = function ($admin) {
    $admin->token || $admin->token = md5(($admin->id ? $admin->id . '_' : '') . uniqid(rand() . '_'));
    return $admin->save();
  };

  $posts = Input::post();

  if ($error = Validator::check($validator, $posts, $admin))
    return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'failure', 'msg' => $error, 'params' => $posts]);

  if ($error = transaction($transaction, $admin))
    return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'failure', 'msg' => $error, 'params' => $posts]);

  Session::setData('token', $admin->token);

  return Url::refreshWithFlash(Url::base('admin'), ['type' => 'success', 'msg' => '登入成功！', 'params' => []]);
  // return refresh (URL::base ('admin'), 'flash', array ('type' => 'success', 'msg' => '登入成功！', 'params' => array ()));
});
