<?php

// $f1 = function () {
//   return '1a';
// };
// $f2 = function () {
//   // return Router::current()
//   //              ->setWork(['你權限未過'])
//   //              ->setStatus(500);
// };

Router::get('/aaa/(user:num)/bbb/(title:any)')
      ->work(function() {
        // $user = Router::current()->getParams('user');
        // $book = new M\BookArticle();

          // M\BookArticle::one()


        // $obj = M\BookArticle::one();

        // $obj = M\BookArticle::one(Where::create('id IN (?)', [2]));
        // $obj = M\BookArticle::one('id = 2');

        // $obj = M\BookArticle::one('id = ?', 2);
        // $obj = M\BookArticle::one('id = ?', 2);

        $obj = M\BookArticle::one(['select' => 'id', 'where' => 'id = 2']);

        echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        var_dump ($obj->createdAt->format ('Y-m-d H:i:s'));
        exit ();
        // $obj = M\BookArticle::one(['where' => ['id = ?', 2]]);
        // $obj = M\BookArticle::one(['where' => ['id = ?', 2], 'select' => 'id', 'order' => 'id ASC, ss DESC']);

        // M\BookArticle::first();
        // M\BookArticle::last(['order' => 'id ASC, ss DESC']);
        // M\BookArticle::all();

        // M\BookArticle::find('one', Where::create('id = ?', 2));
        // M\BookArticle::find('one', ['where' => ['id = ?', 2], 'order' => 'id DESC']);
        // M\BookArticle::find('one', 'id = ?', 2);


        // M\BookArticle::find('one');


        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // var_dump ($book);
        // exit ();
        
        return ;
      });

// Router::get('bbb')->work('get Hello 2');

// Router::post('/aaa/(:id)/bbb/(:any)')->work(function ($a, $b) {
//   return $a . '----' . $b;
// });
// Router::post('bbb')->work('post Hello 2');

// // Router::cli('bbb', 'Hello 2');


// // Router::get('aaa/(:id)/bbb/(:any)', 'Hello');

// // Router::get('aaa/(:id)/bbb/(:any)', function ($params1, $params2) {
// //   return View::create('...')
// //              ->with('p1', $params1)
// //              ->with('p2', $params2);
// // });


// // Router::get('admin/books/(:id)', function ($a, $b, $role) {
// //   // ...
// //   $a = $role == 'admin' ? 1 : 2;

// //   return View::create('...');
// // })->before ($func1)
// //   ->before ($func2);

// // Router::get('admin/peoples/(:id)', function ($id) {
// //   // ...
// //   return View::create('...');
// // })->before ($func);


// // $func1 = function () {
// //   // 驗證是否登入
// //   $user = User::find_by_token ('adddd');

// //   return $user;
// // };
// // $func2 = function ($user) {
// //   // 驗證是否登入

// //   return $user->role;
// // };

// // $func3 = function ($params1, $params2) {


// //   return View::create('...')
// //              ->with('p1', $params1)
// //              ->with('p2', $params2);
  
// //   return Output::json ([]);
// //   return Output::text ('');
// // };  


// // Router::get('admin/books/(:id)')
// //       // ->before($func1)
// //       // ->before($func2)
// //       ->exec ($func3);
