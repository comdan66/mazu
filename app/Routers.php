<?php defined('MAZU') || exit('此檔案不允許讀取！');

// $f1 = function () {
//   return '1a';
// };
// $f2 = function () {
//   // return Router::current()
//   //              ->setWork(['你權限未過'])
//   //              ->setStatus(500);
// };


Router::cli('/')
      ->work(function() {

      });
Router::get('/')
      ->work(function() {

        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // var_dump (Input::ip());
        Load::sysLib('Session.php');
        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // var_dump (Session::setTmpData('a', 'b', 5));
        // var_dump (Session::getTmpData('a'));
        // var_dump (Session::setFlashData('a', 'b'));
        // var_dump (Session::getData('a'));
        // exit ();;


        // $obj = M\Article::one('id = 1');
        
// echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
// var_dump ($obj->cover->putUrl('https://danwoog.files.wordpress.com/2018/02/pic-saugatuck-river-mark-molesworth.jpg'));
//         echo $obj->cover->url();
        
        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // var_dump ($obj->cover->put(PATH_TMP . 'README.md'));
        // exit ();;
        // $user = Router::current()->getParams('user');
        // $book = new M\BookArticle();

        // M\BookArticle::one()


        // $obj = M\BookArticle::one(Where::create('id IN (?)', [2]));
        // $obj = M\BookArticle::one('id = 2');

        // $obj = M\BookArticle::one('id = ?', 2);
        // $obj = M\BookArticle::one('id = ?', 2);
        
        // throw new Exception('dd');

        // gg('asd');

        // $obj = M\Article::one();
        // // // $b = function (){};
        // $a = [1,2,3,['a', '', ['a1' => 1, 'b2' => $obj, new Book()]]];
        // // gg($a);
        // Log::info($a);

        // gg($a);
        // 1/0;
        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // echo dump($a);


        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // echo dump($a);

        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // var_dump (dump('ad'));
        // var_dump (dump([M\Article::one(), 1, 'abc']));
        // $obj = M\Article::one();
        // echo dump([new Book()]);
        // exit ();;


        // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
        // var_dump (json_encode(M\Article::one()));
        // exit ();
        // Log::error('a');

// 1/0;
        // 
        

// gg('asd');
// gg(\M\Article::one());
// gg([\M\Article::one()]);

        // $obj = \M\Article::one();
        // var_dump ($obj);

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
