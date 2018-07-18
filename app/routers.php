<?php defined('MAZU') || exit('此檔案不允許讀取！');

Router::get('admin/logout')->controller('admin/Auth@logout')->alias('AdminLogout');
Router::get('admin/login')->controller('admin/Auth@login')->alias('AdminLogin');
Router::post('admin/login')->controller('admin/Auth@signin')->alias('AdminSignin');
Router::get('admin')->controller('admin/Main@index')->alias('AdminMain');

// Admin Tag
Router::dir('admin', 'Admin', function() {
  Router::get('tags')->controller('Tag@index')->alias('TagIndex');
  Router::get('tags/add')->controller('Tag@add')->alias('TagAdd');
  Router::post('tags')->controller('Tag@create')->alias('TagCreate');
  Router::get('tags/(id:num)/edit')->controller('Tag@edit')->alias('TagEdit');
  Router::put('tags/(id:num)')->controller('Tag@update')->alias('TagUpdate');
  Router::get('tags/(id:num)')->controller('Tag@show')->alias('TagShow');
  Router::delete('tags/(id:num)')->controller('Tag@delete')->alias('TagDelete');
  Router::post('tags/sort')->controller('Tag@sort')->alias('TagSort');
  Router::post('tags/(id:num)/enable')->controller('Tag@enable')->alias('TagEnable');

  // Article
  Router::get('articles')->controller('Article@index')->alias('ArticleIndex');
  Router::get('articles/add')->controller('Article@add')->alias('ArticleAdd');
  Router::post('articles/')->controller('Article@create')->alias('ArticleCreate');
  Router::get('articles/(id:num)/edit')->controller('Article@edit')->alias('ArticleEdit');
  Router::put('articles/(id:num)')->controller('Article@update')->alias('ArticleUpdate');
  Router::get('articles/(id:num)')->controller('Article@show')->alias('ArticleShow');
  Router::delete('articles/(id:num)')->controller('Article@delete')->alias('ArticleDelete');
  Router::post('articles/(id:num)/enable')->controller('Article@enable')->alias('ArticleEnable');

  // Tag 下的 Article
  Router::get('tag/(tagId:num)/articles')->controller('TagArticle@index')->alias('TagArticleIndex');
  Router::get('tag/(tagId:num)/articles/add')->controller('TagArticle@add')->alias('TagArticleAdd');
  Router::post('tag/(tagId:num)/articles/')->controller('TagArticle@create')->alias('TagArticleCreate');
  Router::get('tag/(tagId:num)/articles/(id:num)/edit')->controller('TagArticle@edit')->alias('TagArticleEdit');
  Router::put('tag/(tagId:num)/articles/(id:num)')->controller('TagArticle@update')->alias('TagArticleUpdate');
  Router::get('tag/(tagId:num)/articles/(id:num)')->controller('TagArticle@show')->alias('TagArticleShow');
  Router::delete('tag/(tagId:num)/articles/(id:num)')->controller('TagArticle@delete')->alias('TagArticleDelete');
  Router::post('tag/(tagId:num)/articles/(id:num)/enable')->controller('TagArticle@enable')->alias('TagArticleEnable');
});




// Router::get('/admin/logout')->router('Auth/logout.php');

// Router::get('/admin/logout')->work(function() {
//   Load::sysLib('Session.php');
//   Session::unsetData('token');
//   return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'success', 'msg' => '登出成功！', 'params' => []]);
// });

// Router::get('/admin/login')->work(function() {
//   Load::sysLib('Asset.php');
//   Load::sysLib('Session.php');

//   $flash = Session::getFlashData('flash');

//   $asset = Asset::create()
//                 ->addCSS('/asset/css/icon-login.css')
//                 ->addCSS('/asset/css/admin/login.css')
//                 ->addJS('/asset/js/res/jquery-1.10.2.min.js')
//                 ->addJS('/asset/js/login.js');

//   return View::create('admin/auth/login.php')
//              ->with('asset', $asset)
//              ->with('flash', $flash);
// });

// Router::post('/admin/login')->work(function() {
//   Load::sysLib('Validator.php');
//   Load::sysLib('Session.php');

//   $validator = function(&$posts, &$admin) {
//     isset($posts['account'])  || Validator::error('帳號不存在！');
//     isset($posts['password']) || Validator::error('密碼不存在！');

//     $posts['account']  = trim($posts['account']);
//     $posts['password'] = trim($posts['password']);

//     $posts['account']  || Validator::error('帳號不存在！');
//     $posts['password'] || Validator::error('密碼不存在！');

//     $posts['account'] = strip_tags($posts['account']);
//     $posts['password'] = strip_tags($posts['password']);

//     $admin = \M\Admin::one('account = ?', $posts['account']);
//     $admin || Validator::error('此帳號不存在！');

//     password_verify($posts['password'], $admin->password) || Validator::error('密碼錯誤！');
//   };

//   $transaction = function ($admin) {
//     $admin->token || $admin->token = md5(($admin->id ? $admin->id . '_' : '') . uniqid(rand() . '_'));
//     return $admin->save();
//   };

//   $posts = Input::post();

//   if ($error = Validator::check($validator, $posts, $admin))
//     return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'failure', 'msg' => $error, 'params' => $posts]);

//   if ($error = transaction($transaction, $admin))
//     return Url::refreshWithFlash(Url::base('admin', 'login'), ['type' => 'failure', 'msg' => $error, 'params' => $posts]);

//   Session::setData('token', $admin->token);

//   return Url::refreshWithFlash(Url::base('admin'), ['type' => 'success', 'msg' => '登入成功！', 'params' => []]);
//   // return refresh (URL::base ('admin'), 'flash', array ('type' => 'success', 'msg' => '登入成功！', 'params' => array ()));
// });
