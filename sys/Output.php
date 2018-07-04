<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Output {
  static function text($str) {
    echo $str;
  }
  static function json($json) {
    echo json_encode($json);
  }
  static function router($router) {
    if (!$router)
      return gg('迷路惹！', 404);

    responseStatusHeader($router->getStatus());

    if (($exec = $router->exec()) === null)
      return self::text('');

    if (is_string($exec))
      return self::text($exec);

    if (is_array($exec))
      return self::json($exec);

    if ($exec instanceOf View)
      return self::text($exec->get());
  }
}