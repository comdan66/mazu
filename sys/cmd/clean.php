<?php defined('MAZU') || exit('此檔案不允許讀取！');

Load::sysFunc('dir.php') || exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('Clean 初始化失敗！' . str_repeat(' ', CLI_LEN - 26), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");

if (!function_exists('headerText')) {
  function headerText($cho = null) {
    system('clear');

    echo "\n";
    echo ' ' . cliColor('【功能選項】', 'W') . "\n";
    echo cliColor($cho == '1' ? '  ➜' : '   ', 'y') . cliColor('  1. ', $cho == '1' ? 'Y' : null) . cliColor('清除全部目錄',       $cho == '1' ? 'Y' : null) . "\n";
    echo cliColor($cho == '2' ? '  ➜' : '   ', 'y') . cliColor('  2. ', $cho == '2' ? 'Y' : null) . cliColor('清除 Cache 目錄',   $cho == '2' ? 'Y' : null) . "\n";
    echo cliColor($cho == '3' ? '  ➜' : '   ', 'y') . cliColor('  3. ', $cho == '3' ? 'Y' : null) . cliColor('清除 Tmp 目錄',     $cho == '3' ? 'Y' : null) . "\n";
    echo cliColor($cho == '4' ? '  ➜' : '   ', 'y') . cliColor('  4. ', $cho == '4' ? 'Y' : null) . cliColor('清除 Session 目錄', $cho == '4' ? 'Y' : null) . "\n";
    echo cliColor($cho == 'q' ? '  ➜' : '   ', 'y') . cliColor('  q. ', $cho == 'q' ? 'Y' : null) . cliColor('離開本程式～',       $cho == 'q' ? 'Y' : null) . "\n";

    return true;
  }
}

if (!function_exists('cho1')) {
  function cho1($echo = true) {
    $files = arrayFlatten(array_filter([PATH_CACHE, PATH_TMP, PATH_SESSION], function($dir) { return array_filter(dirMap($dir), function($file) use($dir) { return $file !== 'index.html' ? !@unlink($dir . $file) ? ($dir . $file) : false : false; }); }));

    if ($files)
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('清除失敗！', 'r') . ' 無法清除的資訊如下：' . "\n" . implode("\n", array_map(function($file) { return '  ' . cliColor('➜', 'G') . ' 檔案位置：' . cliColor($file, 'W'); }, $files)) . "\n\n");
      else
        exit(json_encode(['status' => 0, 'msgs' => ['錯誤原因' => '清除失敗！', '無法刪除的檔案' => $files]]));
    else
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('◎', 'G') . " 全部已清除完畢！" . "\n\n");
      else
        exit(json_encode(['status' => 1, 'msgs' => []]));
  }
}

if (!function_exists('cho2')) {
  function cho2($echo = true) {
    $files = arrayFlatten(array_filter([PATH_CACHE], function($dir) { return array_filter(dirMap($dir), function($file) use($dir) { return $file !== 'index.html' ? !@unlink($dir . $file) ? ($dir . $file) : false : false; }); }));

    if ($files)
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('清除失敗！', 'r') . ' 無法清除的資訊如下：' . "\n" . implode("\n", array_map(function($file) { return '  ' . cliColor('➜', 'G') . ' 檔案位置：' . cliColor($file, 'W'); }, $files)) . "\n\n");
      else
        exit(json_encode(['status' => 0, 'msgs' => ['錯誤原因' => '清除失敗！', '無法刪除的檔案如下' => $files]]));
    else
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('◎', 'G') . " " . cliColor("Cache 目錄", 'W') . "全部已清除完畢！" . "\n\n");
      else
        exit(json_encode(['status' => 1, 'msgs' => []]));
  }
}

if (!function_exists('cho3')) {
  function cho3($echo = true) {
    $files = arrayFlatten(array_filter([PATH_TMP], function($dir) { return array_filter(dirMap($dir), function($file) use($dir) { return $file !== 'index.html' ? !@unlink($dir . $file) ? ($dir . $file) : false : false; }); }));

    if ($files)
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('清除失敗！', 'r') . ' 無法清除的資訊如下：' . "\n" . implode("\n", array_map(function($file) { return '  ' . cliColor('➜', 'G') . ' 檔案位置：' . cliColor($file, 'W'); }, $files)) . "\n\n");
      else
        exit(json_encode(['status' => 0, 'msgs' => ['錯誤原因' => '清除失敗！', '無法刪除的檔案' => $files]]));
    else
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('◎', 'G') . " " . cliColor("Tmp 目錄", 'W') . "全部已清除完畢！" . "\n\n");
      else
        exit(json_encode(['status' => 1, 'msgs' => []]));
  }
}

if (!function_exists('cho4')) {
  function cho4() {
    $files = arrayFlatten(array_filter([PATH_SESSION], function($dir) { return array_filter(dirMap($dir), function($file) use($dir) { return $file !== 'index.html' ? !@unlink($dir . $file) ? ($dir . $file) : false : false; }); }));

    if ($files)
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('清除失敗！', 'r') . ' 無法清除的資訊如下：' . "\n" . implode("\n", array_map(function($file) { return '  ' . cliColor('➜', 'G') . ' 檔案位置：' . cliColor($file, 'W'); }, $files)) . "\n\n");
      else
        exit(json_encode(['status' => 0, 'msgs' => ['錯誤原因' => '清除失敗！', '無法刪除的檔案' => $files]]));
    else
      if ($echo)
        exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n " . cliColor('◎', 'G') . " " . cliColor("Session 目錄", 'W') . "全部已清除完畢！" . "\n\n");
      else
        exit(json_encode(['status' => 1, 'msgs' => []]));
  }
}


if (is_string(Router::params(0))) {
  switch (Router::params(0)) {
    case 'cache':
      cho2(Router::params(1) !== 'deploy');
      break;

    case 'tmp':
      cho3(Router::params(1) !== 'deploy');
      break;

    case 'session':
      cho4(Router::params(1) !== 'deploy');
      break;

    default:
    case 'all':
      cho1(Router::params(1) !== 'deploy');
      break;
  }
} else {
  do {
    headerText();
    echo "\n " . cliColor('➜', 'R') . ' 請輸入您的選項' .  cliColor('(q)', 'N') . '：';
    ($cho = strtolower(trim(fgets(STDIN)))) || $cho = 'q';
  } while (!in_array($cho, ['1', '2', 'q']));
}

$cho === '1' && cho1();
$cho === '2' && cho2();
$cho === '3' && cho3();
$cho === '4' && cho4();

headerText('q');
$cho === 'q' && exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n" . "  好的！期待您下次再使用，" . cliColor('掰掰', 'W') . "～  \n\n");
