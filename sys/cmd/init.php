<?php defined('MAZU') || exit('此檔案不允許讀取！');

$dirs = ['log', 'cache', 'storage', 'session', 'tmp', 'asset'];
Load::sysFunc('file.php');

if (!function_exists('headerText')) {
  function headerText($cho = null) {
    system('clear');

    echo "\n";
    echo ' ' . cliColor('【環境選項】', 'W') . "\n";
    echo cliColor($cho == '1' ? '  ➜' : '   ', 'y') . cliColor('  1. ', $cho == '1' ? 'Y' : null) . cliColor('開發環境', $cho == '1' ? 'Y' : null) . cliColor('(development)', $cho == '1' ? 'y' : 'N') . "\n";
    echo cliColor($cho == '2' ? '  ➜' : '   ', 'y') . cliColor('  2. ', $cho == '2' ? 'Y' : null) . cliColor('測試環境', $cho == '2' ? 'Y' : null) . cliColor('(testing)',     $cho == '2' ? 'y' : 'N') . "\n";
    echo cliColor($cho == '3' ? '  ➜' : '   ', 'y') . cliColor('  3. ', $cho == '3' ? 'Y' : null) . cliColor('正式環境', $cho == '3' ? 'Y' : null) . cliColor('(production)',  $cho == '3' ? 'y' : 'N') . "\n";
    echo cliColor($cho == 'q' ? '  ➜' : '   ', 'y') . cliColor('  q. ', $cho == 'q' ? 'Y' : null) . cliColor('離開本程式～', $cho == 'q' ? 'Y' : null) . "\n";

    return true;
  }
}

if (!function_exists ('writeIndex')) {
  function writeIndex($path) {
    return file_exists($path .=  DIRECTORY_SEPARATOR . 'index.html') ? true : fileWrite($path, "<!DOCTYPE html>\n" . "<html>\n" . "<head>\n" . "  <meta http-equiv=\"Content-type\" content=\"text/html; charset=utf-8\" />\n" . "  <title>403 禁止訪問</title>\n" . "</head>\n" . "<body>\n" . "\n" . "<p>您無權查看該網頁。</p>\n" . "\n" . "</body>\n" . "</html>");
  }
}

if (is_numeric(Router::params(0)) && in_array(Router::params(0), ['1', '2', '3'], true)) {
  $cho = Router::params(0);
} else {
  do {
    headerText();
    echo "\n " . cliColor('➜', 'R') . ' 請輸入您的選項' .  cliColor('(q)', 'N') . '：';
    ($cho = strtolower(trim(fgets(STDIN)))) || $cho = 'q';
  } while (!in_array($cho, ['1', '2', '3', 'q']));
}

headerText($cho);
$cho === 'q' && exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n" . "  好的！期待您下次再使用，" . cliColor('掰掰', 'W') . "～  \n\n");

echo "\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n";

$env = '';
$env .= ($cho === '1' ? '' : '// ') . "define('ENVIRONMENT', 'development');" . "\n";
$env .= ($cho === '2' ? '' : '// ') . "define('ENVIRONMENT', 'testing');" . "\n";
$env .= ($cho === '3' ? '' : '// ') . "define('ENVIRONMENT', 'production');" . "\n";

$env = "<?php defined('MAZU') || exit('此檔案不允許讀取！');\n\n\n/* ------------------------------------------------------\n *  定義環境常數\n * ------------------------------------------------------ */\n\n" . $env . "\n\nswitch (ENVIRONMENT) {\n  case 'development':\n  case 'testing':\n    ini_set('display_errors', 1);\n    error_reporting(-1);\n    break;\n\n  case 'production':\n    ini_set('display_errors', 0);\n    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);\n    break;\n\n  default:\n    new GG('「環境變數(ENVIRONMENT)」設定錯誤！', 503);\n    break;\n}\n";
exit ("\n" . ' ' . cliColor('◎', 'G') . " 寫入環境設定 - " . (fileWrite(PATH . 'env.php', $env, 'w+b') ? cliColor('成功', 'g') : cliColor('失敗', 'r'))
    . "\n"
    . "\n" . ($dirs ? implode("\n", array_map(function($dir) {
      $dir = PATH . $dir;
      $return = ' ' . cliColor('◎', 'G') . " 新增 " . cliColor($dir, 'W') . ' 資料夾 - ';
      
      if (is_file($dir))
        return $return . cliColor('失敗', 'r') . ' - ' . cliColor('存在一樣檔名的檔案！', 'R');

      if (!is_dir($dir) && !umaskMkdir($dir, 0777, true))
        return $return . cliColor('失敗', 'r') . ' - ' . cliColor('產生資料夾失敗！', 'R');

      $return .= cliColor('成功', 'g');

      if (is_file($dir . DIRECTORY_SEPARATOR . 'index.html'))
        return $return . ' - ' . cliColor('index.html 已存在！', 'N');

      if (!writeIndex($dir))
        return $return . ' - ' . cliColor('index.html 補充失敗！', 'R');

      return $return . ' - ' . cliColor('index.html 新增成功！', 'N');
    }, $dirs)) . "\n" : '') . "\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('➜', 'R') . " " . cliColor($env == '2' ? '正式環境' : '開發環境', 'W') . cliColor($env == '2' ? '(production)' : '(development)', 'N') . " 初始化" . cliColor('順利完成', 'W') . '囉，' . ($env == '2' ? '快開啟網址確認一下吧' : '可以開始寫程式啦') . "！\n\n");
