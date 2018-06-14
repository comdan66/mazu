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

//取得此專案資料夾之絕對位置
define('PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// sys 的絕對位置
define('PATH_SYS', PATH . 'sys' . DIRECTORY_SEPARATOR);

// app 的絕對位置
define('PATH_APP', PATH . 'app' . DIRECTORY_SEPARATOR);

if(!@include_once PATH_SYS . 'Common.php')
  exit('初始化失敗！');

if (!isPhpVersion('5.6'))
  exit('PHP 版本太舊，請大於等於 5.6');



// var_dump (chr($a), chr($a | 32));
exit ();

//    0000 0011
// |) 0000 0100
// -------------
//    0000 0111 => 7



exit ();