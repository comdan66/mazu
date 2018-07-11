<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Validator {
  public static function check($closure, &...$args) {
    if (!is_callable($closure))
      return gg('Validator Check 第一個參數必須可呼叫！');

    try {
      call_user_func_array($closure, $args);
      return '';
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public static function error($msg) {
    throw new Exception($msg);
  }
}
