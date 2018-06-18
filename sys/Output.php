<?php

class Output {
  static function text($str) {
    echo $str;
  }
  static function json($json) {
    echo json_encode($json);
  }
  static function router($router) {
    if (!$router) {
      responseStatusHeader(404);
      return self::text(View::maybe('error/404.php')->get());
    }

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