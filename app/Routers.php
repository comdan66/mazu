<?php

$f1 = function () {
  return '1a';
};
$f2 = function () {
  // return Router::current()
  //              ->setWork(['你權限未過'])
  //              ->setStatus(500);
};

Router::get('/aaa/(user:num)/bbb/(title:any)')
      ->before($f1)
      ->before($f2)
      ->work(function() {
        // $user = Router::current()->getParams('user');
        return ;
      })
      ->after($f1)
      ->after($f2);

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
