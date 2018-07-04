<?php

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, MAZU
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

// 定義時區
date_default_timezone_set('Asia/Taipei');

// 定義版號
define('MAZU', '1.0.0');



/* ------------------------------------------------------
 *  定義路徑常數
 * ------------------------------------------------------ */

define('PATH', dirname(__FILE__)        . DIRECTORY_SEPARATOR); // 此專案資料夾絕對位置
define('PATH_SYS',   PATH .     'sys'   . DIRECTORY_SEPARATOR); // sys 絕對位置
define('PATH_LOG',   PATH .     'log'   . DIRECTORY_SEPARATOR); // log 絕對位置
define('PATH_APP',   PATH .     'app'   . DIRECTORY_SEPARATOR); // app 絕對位置
define('PATH_VIEW',  PATH_APP . 'view'  . DIRECTORY_SEPARATOR); // view 絕對位置
define('PATH_MODEL', PATH_APP . 'model' . DIRECTORY_SEPARATOR); // model 絕對位置



/* ------------------------------------------------------
 *  載入初始函式
 * ------------------------------------------------------ */

if (!@include_once PATH_SYS . 'CommonFunc.php')
  exit('載入 CommonFunc 失敗！');



/* ------------------------------------------------------
 *  只允許包含 5.6 版本以上使用
 * ------------------------------------------------------ */

isPhpVersion('5.6')              || gg('PHP 版本太舊，請大於等於 5.6 版本！');

load(PATH_SYS . 'Benchmark.php') || gg('載入 Benchmark 失敗！');
Benchmark::markStar('整體');

load(PATH     . 'Env.php')       || gg('載入 Env 失敗！');
load(PATH_SYS . 'View.php')      || gg('載入 View 失敗！');
load(PATH_SYS . 'Charset.php')   || gg('載入 Charset 失敗！');
load(PATH_SYS . 'Log.php')       || gg('載入 Log 失敗！');
load(PATH_SYS . 'Url.php')       || gg('入載 Url 失敗！');
load(PATH_SYS . 'Router.php')    || gg('入載 Router 失敗！');

load(PATH_SYS . 'Output.php')    || gg('入載 Output 失敗！');
// load(PATH_SYS . 'Model.php')     || gg('入載 Model 失敗！');



Output::router (Router::current());






Log::closeAll();
Benchmark::markEnd('整體');

echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
var_dump(Benchmark::elapsedTime());
var_dump(Benchmark::elapsedMemory());
exit ();
