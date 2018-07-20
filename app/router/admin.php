<?php defined('MAZU') || exit('此檔案不允許讀取！');

// Admin
Router::dir('admin', 'Admin', function() {

  // 登入
  Router::get('logout')->controller('Auth@logout');
  Router::get('login')->controller('Auth@login');
  Router::post('login')->controller('Auth@signin');

  // 後台主頁
  Router::get()->controller('Main@index');

  // Tag
  Router::get('tags')->controller('Tag@index');
  Router::get('tags/add')->controller('Tag@add');
  Router::post('tags')->controller('Tag@create');
  Router::get('tags/(id:num)/edit')->controller('Tag@edit');
  Router::put('tags/(id:num)')->controller('Tag@update');
  Router::get('tags/(id:num)')->controller('Tag@show');
  Router::delete('tags/(id:num)')->controller('Tag@delete');
  Router::post('tags/sort')->controller('Tag@sort');

  // Article
  Router::get('articles')->controller('Article@index');
  Router::get('articles/add')->controller('Article@add');
  Router::post('articles/')->controller('Article@create');
  Router::get('articles/(id:num)/edit')->controller('Article@edit');
  Router::put('articles/(id:num)')->controller('Article@update');
  Router::get('articles/(id:num)')->controller('Article@show');
  Router::delete('articles/(id:num)')->controller('Article@delete');
  Router::post('articles/(id:num)/enable')->controller('Article@enable');

  // Tag 下的 Article
  Router::get('tag/(tagId:num)/articles')->controller('TagArticle@index');
  Router::get('tag/(tagId:num)/articles/add')->controller('TagArticle@add');
  Router::post('tag/(tagId:num)/articles/')->controller('TagArticle@create');
  Router::get('tag/(tagId:num)/articles/(id:num)/edit')->controller('TagArticle@edit');
  Router::put('tag/(tagId:num)/articles/(id:num)')->controller('TagArticle@update');
  Router::get('tag/(tagId:num)/articles/(id:num)')->controller('TagArticle@show');
  Router::delete('tag/(tagId:num)/articles/(id:num)')->controller('TagArticle@delete');
  Router::post('tag/(tagId:num)/articles/(id:num)/enable')->controller('TagArticle@enable');

  // Admin
  Router::get('admins')->controller('Admin@index');
  Router::get('admins/add')->controller('Admin@add');
  Router::post('admins')->controller('Admin@create');
  Router::get('admins/(id:num)/edit')->controller('Admin@edit');
  Router::put('admins/(id:num)')->controller('Admin@update');
  Router::get('admins/(id:num)')->controller('Admin@show');
  Router::delete('admins/(id:num)')->controller('Admin@delete');
  
  // Backup
  Router::get('backups')->controller('Backup@index');
  Router::get('backups/(id:num)')->controller('Backup@show');
  Router::post('backups/(id:num)/unwatch')->controller('Backup@unwatch');
});