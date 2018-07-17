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

define('PATH', dirname(__FILE__)                   . DIRECTORY_SEPARATOR); // 此專案資料夾絕對位置
define('PATH_SYS',        PATH .     'sys'         . DIRECTORY_SEPARATOR); // sys 絕對位置
define('PATH_LOG',        PATH .     'log'         . DIRECTORY_SEPARATOR); // log 絕對位置
define('PATH_TMP',        PATH .     'tmp'         . DIRECTORY_SEPARATOR); // tmp 絕對位置
define('PATH_CACHE',      PATH .     'cache'       . DIRECTORY_SEPARATOR); // cache 絕對位置
define('PATH_SESSION',    PATH .     'session'     . DIRECTORY_SEPARATOR); // session 絕對位置
define('PATH_APP',        PATH .     'app'         . DIRECTORY_SEPARATOR); // app 絕對位置
define('PATH_CORE',       PATH_APP . 'core'        . DIRECTORY_SEPARATOR); // core 絕對位置
define('PATH_CONTROLLER', PATH_APP . 'controller'  . DIRECTORY_SEPARATOR); // controller 絕對位置
define('PATH_MIGRATION',  PATH_APP . 'migration'   . DIRECTORY_SEPARATOR); // migration 絕對位置
define('PATH_VIEW',       PATH_APP . 'view'        . DIRECTORY_SEPARATOR); // view 絕對位置
define('PATH_MODEL',      PATH_APP . 'model'       . DIRECTORY_SEPARATOR); // model 絕對位置
define('PATH_SYS_CORE',   PATH_SYS . 'core'        . DIRECTORY_SEPARATOR); // sys core 絕對位置
define('PATH_SYS_LIB',    PATH_SYS . 'lib'         . DIRECTORY_SEPARATOR); // sys lib 絕對位置
define('PATH_SYS_FUNC',   PATH_SYS . 'func'        . DIRECTORY_SEPARATOR); // sys func 絕對位置
define('PATH_SYS_MODEL',  PATH_SYS . 'model'       . DIRECTORY_SEPARATOR); // sys model 絕對位置
define('PATH_SYS_CMD',    PATH_SYS . 'cmd'         . DIRECTORY_SEPARATOR); // sys cmd 絕對位置


/* ------------------------------------------------------
 *  載入初始函式
 * ------------------------------------------------------ */

if (!@include_once PATH_SYS_CORE . 'Common.php')
  exit('載入 Common 失敗！');



/* ------------------------------------------------------
 *  載入環境常數 ENVIRONMENT
 * ------------------------------------------------------ */

defined('ENVIRONMENT') || Load::path('env.php') || gg('載入環境常數失敗！');



/* ------------------------------------------------------
 *  載入相關物件
 * ------------------------------------------------------ */

Load::sysCore('Benchmark.php')  || gg('載入 Benchmark 失敗！');
Benchmark::markStar('整體');

Load::sysCore('View.php')       || gg('載入 View 失敗！');
Load::sysCore('Charset.php')    || gg('載入 Charset 失敗！');
Load::sysCore('Log.php')        || gg('載入 Log 失敗！');
Load::sysCore('Url.php')        || gg('載入 Url 失敗！');
Load::sysCore('Controller.php') || gg('載入 Controller 失敗！');
Load::sysCore('Router.php')     || gg('載入 Router 失敗！');
Load::sysCore('Output.php')     || gg('載入 Output 失敗！');
Load::sysCore('Model.php')      || gg('載入 Model 失敗！');
Load::sysCore('Security.php')   || gg('載入 Security 失敗！');
Load::sysCore('Input.php')      || gg('載入 Input 失敗！');

if (config('other', 'autoLoadComposer'))
  Load::path('vendor' . DIRECTORY_SEPARATOR . 'autoload.php') || gg('載入 Composer 失敗！');



/* ------------------------------------------------------
 *  輸出結果
 * ------------------------------------------------------ */

Output::router(Router::current());



/* ------------------------------------------------------
 *  結束
 * ------------------------------------------------------ */

// defined('MODEL_LOADED') && \_M\Connection::instance()->close();
Benchmark::markEnd('整體');
Log::benchmark("耗時：" . Benchmark::elapsedTime('整體'), "記憶體：" . Benchmark::elapsedMemory('整體'));
Log::closeAll();
