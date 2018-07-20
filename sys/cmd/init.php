<?php defined('MAZU') || exit('此檔案不允許讀取！');

$dirs = ['log', 'cache', 'storage', 'session', 'tmp', 'asset'];
Load::sysFunc('file.php');

if (!function_exists('headerText')) {
  function headerText($cho = null) {
    system('clear');

    echo "\n";
    echo ' ' . cc('【環境選項】', 'W') . "\n";
    echo cc($cho == '1' ? '  ➜' : '   ', 'y') . cc('  1. ', $cho == '1' ? 'Y' : null) . cc('開發環境', $cho == '1' ? 'Y' : null) . cc('(development)', $cho == '1' ? 'y' : 'N') . "\n";
    echo cc($cho == '2' ? '  ➜' : '   ', 'y') . cc('  2. ', $cho == '2' ? 'Y' : null) . cc('測試環境', $cho == '2' ? 'Y' : null) . cc('(testing)',     $cho == '2' ? 'y' : 'N') . "\n";
    echo cc($cho == '3' ? '  ➜' : '   ', 'y') . cc('  3. ', $cho == '3' ? 'Y' : null) . cc('正式環境', $cho == '3' ? 'Y' : null) . cc('(production)',  $cho == '3' ? 'y' : 'N') . "\n";
    echo cc($cho == 'q' ? '  ➜' : '   ', 'y') . cc('  q. ', $cho == 'q' ? 'Y' : null) . cc('離開本程式～', $cho == 'q' ? 'Y' : null) . "\n";

    return true;
  }
}

if (!function_exists ('writeIndex')) {
  function writeIndex($path) {
    return file_exists($path .=  DIRECTORY_SEPARATOR . 'index.html') ? true : fileWrite($path, '<!DOCTYPE html><html lang="tw"><head><meta http-equiv="Content-Language" content="zh-tw" /><meta http-equiv="Content-type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui" /><title>403 禁止訪問</title><style type="text/css">*,*:after,*:before{ vertical-align:top; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; box-sizing:border-box; -moz-osx-font-smoothing:antialiased; -webkit-font-smoothing:antialiased; -moz-font-smoothing:antialiased; -ms-font-smoothing:antialiased; -o-font-smoothing:antialiased }*::-moz-selection,*:after::-moz-selection,*:before::-moz-selection{ color:#fff; background-color:#96c8ff }*::selection,*:after::selection,*:before::selection{ color:#fff; background-color:#96c8ff }html{ height:100% }html body{ font-family:Arial, "微軟正黑體", "Microsoft JhengHei", -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"; height:auto; text-align:center; margin:0; padding:0; font-size:medium; font-weight:normal; color:#555; background-color:#fff; background-color:#f9fafe; position:relative; display:inline-block; width:100%; min-height:100% }#main{ position:fixed; left:0; top:calc(50% - 64px / 2); display:inline-block; width:100%; height:64px; line-height:32px; text-align:center; font-size:18px }#main a.active,#main a:hover{ color:#0d5bdd }</style></head><body lang="zh-tw"><main id="main">您無權查看該網頁！</main></body></html>');
  }
}

if (is_numeric(Router::params(0)) && in_array(Router::params(0), ['1', '2', '3'], true)) {
  $cho = Router::params(0);
} else {
  do {
    headerText();
    echo "\n " . cc('➜', 'R') . ' 請輸入您的選項' .  cc('(q)', 'N') . '：';
    ($cho = strtolower(trim(fgets(STDIN)))) || $cho = 'q';
  } while (!in_array($cho, ['1', '2', '3', 'q']));
}

headerText($cho);
$cho === 'q' && exit("\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n\n" . "  好的！期待您下次再使用，" . cc('掰掰', 'W') . "～  \n\n");

echo "\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n";

$env = '';
$env .= ($cho === '1' ? '' : '// ') . "define('ENVIRONMENT', 'development');" . "\n";
$env .= ($cho === '2' ? '' : '// ') . "define('ENVIRONMENT', 'testing');" . "\n";
$env .= ($cho === '3' ? '' : '// ') . "define('ENVIRONMENT', 'production');" . "\n";

$env = "<?php defined('MAZU') || exit('此檔案不允許讀取！');\n\n\n/* ------------------------------------------------------\n *  定義環境常數\n * ------------------------------------------------------ */\n\n" . $env . "\n\nswitch (ENVIRONMENT) {\n  case 'development':\n  case 'testing':\n    ini_set('display_errors', 1);\n    error_reporting(-1);\n    break;\n\n  case 'production':\n    ini_set('display_errors', 0);\n    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);\n    break;\n\n  default:\n    new GG('「環境變數(ENVIRONMENT)」設定錯誤！', 503);\n    break;\n}\n";
exit ("\n" . ' ' . cc('◎', 'G') . " 寫入環境設定 - " . (fileWrite(PATH . 'env.php', $env, 'w+b') ? cc('成功', 'g') : cc('失敗', 'r'))
    . "\n"
    . "\n" . ($dirs ? implode("\n", array_map(function($dir) {
      $dir = PATH . $dir;
      $return = ' ' . cc('◎', 'G') . " 新增 " . cc($dir, 'W') . ' 資料夾 - ';
      
      if (is_file($dir))
        return $return . cc('失敗', 'r') . ' - ' . cc('存在一樣檔名的檔案！', 'R');

      if (!is_dir($dir) && !umaskMkdir($dir, 0777, true))
        return $return . cc('失敗', 'r') . ' - ' . cc('產生資料夾失敗！', 'R');

      $return .= cc('成功', 'g');

      if (is_file($dir . DIRECTORY_SEPARATOR . 'index.html'))
        return $return . ' - ' . cc('index.html 已存在！', 'N');

      if (!writeIndex($dir))
        return $return . ' - ' . cc('index.html 補充失敗！', 'R');

      return $return . ' - ' . cc('index.html 新增成功！', 'N');
    }, $dirs)) . "\n" : '') . "\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cc('➜', 'R') . " " . cc($env == '2' ? '正式環境' : '開發環境', 'W') . cc($env == '2' ? '(production)' : '(development)', 'N') . " 初始化" . cc('順利完成', 'W') . '囉，' . ($env == '2' ? '快開啟網址確認一下吧' : '可以開始寫程式啦') . "！\n\n");
