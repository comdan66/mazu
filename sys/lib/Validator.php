<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Validator {
  public static function check($closure, &...$args) {
    is_callable($closure) || gg('Validator Check 第一個參數必須可呼叫！');

    try {
      call_user_func_array($closure, $args);
      return '';
    } catch (Exception $e) {
      Router::setStatus(400);
      return $e->getMessage();
    }
  }

  public static function error($msg) {
    throw new Exception($msg);
  }
}

function validator($closure, &...$args) {
    is_callable($closure) || gg('validator 第一個參數必須可呼叫！');
  return call_user_func_array(['Validator', 'check'], array_merge([$closure], $args));
}
