<?php defined('MAZU') || exit('此檔案不允許讀取！');

class CliController extends Controller {
  
  public function __construct() {
    parent::__construct();

    isCli() || gg('你不是 Command Line 指令！！');

    ini_set('memory_limit', '2048M');
    ini_set('set_time_limit', 60 * 60);
  }
}
