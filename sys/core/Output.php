<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Output {
  private static $zlibOc;
  private static $headers = [];

  public static function zlibOc () {
    return self::$zlibOc !== null ? self::$zlibOc : self::$zlibOc = (bool)ini_get('zlib.output_compression');
  }

  public static function appendHeader ($header, $replace = true) {
    if (self::zlibOc() && strncasecmp($header, 'content-length', 14) === 0)
      return ;

    array_push(self::$headers, array ($header, $replace));
  }

  public static function text($str) {
    self::appendHeader('Content-Type: text/html; charset=UTF-8', true);
    return self::display($str);
  }

  public static function json($json, $code = null) {
    self::appendHeader('Content-Type: application/json; charset=UTF-8', true);
    return self::display(json_encode($json));
  }

  public static function display($text) {
    foreach (self::$headers as $header)
      @header($header[0], $header[1]);
    echo $text;
  }

  public static function router($router) {
    if (!$router)
      return new GG('迷路惹！', 404);

    $exec = $router->exec();
    responseStatusHeader(Router::status());

    if ($exec === null)
      return self::text('');

    if (is_string($exec))
      return self::text($exec);

    if (is_array($exec))
      return self::json($exec);

    if ($exec instanceOf Router)
      return self::text((string)$exec);

    if ($exec instanceOf View)
      return self::text($exec->output());
  }
}