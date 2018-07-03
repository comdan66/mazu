<?php defined('MAZU') || exit('此檔案不允許讀取！');


/* ------------------------------------------------------
 *  定義環境常數
 * ------------------------------------------------------ */

define('ENVIRONMENT', 'testing');
// define('ENVIRONMENT', 'development');
// define('ENVIRONMENT', 'production');


switch (ENVIRONMENT) {
  case 'testing':
  case 'development':
    ini_set('display_errors', 1);
    error_reporting(-1);
    break;

  case 'production':
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
    break;

  default:
    gg('「環境變數(ENVIRONMENT)」設定錯誤！', 503);
    break;
}