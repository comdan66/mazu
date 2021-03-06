<?php defined('MAZU') || exit('此檔案不允許讀取！');

Load::sysLib('Migration.php') || exit("\n" . cc(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cc(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cc(' 警告！ ', 'Y', 'r') . cc('Migration 初始化失敗！' . str_repeat(' ', CLI_LEN - 30), 'W', 'r') . "\n" . cc(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cc(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");

if (!function_exists('headerText')) {
  function headerText($cho = null) {
    system('clear');
    $now   = Migration::nowVersion();
    $files = Migration::files(true);

    echo "\n";
    echo ' ' . cc('【Migration】', 'W') . "\n";

    foreach ($files as $v => $f) {
      $at = Migration::get($f);
      $at = $at['at'];
      $f = preg_replace('/^\d{3}-/', '', basename($f, '.php'));
      echo $now == $v ? '  ' . cc('➜', 'y') . '' . cc(sprintf('%3s.', $v), 'Y') . ' ' . cc(sprintf('%-53s', $f), 'Y') . cc($at, 'y') . "\n" :
                        ' ' . '  '                     . sprintf('%3s.', $v)                . ' ' . sprintf('%-53s', $f)                . cc($at, 'N') . "\n";
    }

    echo "\n";
    echo ' ' . cc('【功能選項】', 'W') . "\n";
    echo cc($cho == '1' ? '  ➜' : '   ', 'y') . cc('  1. ', $cho == '1' ? 'Y' : null) . cc('更新至最新版', $cho == '1' ? 'Y' : null) . "\n";
    echo cc($cho == '2' ? '  ➜' : '   ', 'y') . cc('  2. ', $cho == '2' ? 'Y' : null) . cc('輸入更新版號', $cho == '2' ? 'Y' : null) . "\n";
    echo cc($cho == 'q' ? '  ➜' : '   ', 'y') . cc('  q. ', $cho == 'q' ? 'Y' : null) . cc('離開本程式～', $cho == 'q' ? 'Y' : null) . "\n";

    return true;
  }
}

if (!function_exists('cho1')) {
  function cho1($version = null, $echo = true) {
    $cho  = 1;
    $now  = Migration::nowVersion();
    $keys = array_keys(Migration::files(true));
    
    if ($version !== null)
      is_numeric($version) && $version >= 0 && $version <= end($keys) ? $cho = '2' : exit("\n" . cc(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cc(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cc(' 警告！ ', 'Y', 'r') . cc('版本「', null, 'r') . cc($version, 'W', 'r') . cc('」是錯誤的版號，請使用正確的版號', null, 'r') . cc('(0 ~ ' . end($keys) . ')', 'W', 'r') .  cc(str_repeat(' ', CLI_LEN - 55), null, 'r') . "\n" . cc(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cc(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");
    else
      $version = end($keys);

    if ($echo) {
      headerText($cho);

      $err = Migration::to($version);
      headerText($cho) && $err === true ?
        exit("\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n\n" . cc(' ➤', 'R') . " Migration 更新中，正在由第 " . cc($now, 'W') . ' 版更新至第' . cc($version, 'W') . ' 版' . cc(' ─ ', 'N') . cc('成功', 'g') . "\n" . cc(' ➤', 'R') . ' 目前已經更新至第 ' . cc(Migration::nowVersion(), 'W') . ' 版！' . "\n\n") :
        exit("\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n\n" . cc(' ➤', 'R') . " Migration 更新中，正在由第 " . cc($now, 'W') . ' 版更新至第' . cc($version, 'W') . ' 版' . cc(' ─ ', 'N') . cc('失敗', 'r') . "\n" . cc(' ➤', 'R') . ' 目前版本只更新到 ' . cc(Migration::nowVersion(), 'W') . ' 版！' . "\n\n"
                  . cc(str_repeat('═', CLI_LEN), 'N') . "\n\n" . implode("\n", array_map(function($key, $err) { return ' ' . cc('➤', 'B') . ' ' . $key . '：' . cc($err, 'W') . "\n"; }, array_keys($err), $err)) . "\n\n");
    } else {
      $err = Migration::to($version);
      exit(json_encode(['status' => $err === true ? 1 : 0, 'msgs' => $err === true ? [] : $err, 'now' => Migration::nowVersion()]));
    }
  }
}

if (!function_exists('cho2')) {
  function cho2() {
    $keys = array_keys(Migration::files(true));
    $check = $version = '';
    
    do {
      headerText('2');
      echo "\n " . cc('➜', 'R') . " 請輸入要更新的版本號" . cc('(0 ~ ' . end($keys) . ')', 'N') . "：" . (is_numeric($version) && $version >= 0 && $version <= end($keys) ? $version . "\n" : '');
      is_numeric($version = is_numeric($version) && $version >= 0 && $version <= end($keys) ? $version : trim(fgets(STDIN))) || $version = '';

      if (is_numeric($version) && $version >= 0 && $version <= end($keys)) {
        echo " " . cc('➜', 'R') . ' 您確定要更新至第 ' . cc($version, 'W') . ' 版' . cc('[y：沒錯, n：不是]', 'N') . '：';
        ($check = strtolower(trim(fgets(STDIN)))) == 'n' && $version = '';
      }
    } while($check != 'y');
    
    cho1($version);
  }
}

Migration::files(true) || exit("\n " . cc('◎', 'G') . " 目前沒有任何 Migration！\n\n");

if (is_numeric(Router::params(0)) && Router::params(0) >= 0) {
  cho1(Router::params(0));
} else if (is_string(Router::params(0)) && in_array(Router::params(0), ['new', 'ori'])) {
  switch (Router::params(0)) {
    default:
    case 'new':
      cho1(null, Router::params(1) !== 'deploy');
      break;

    case 'ori':
      cho1(0, Router::params(1) !== 'deploy');
      break;
  }
} else {
  do {
    headerText();
    echo "\n " . cc('➜', 'R') . ' 請輸入您的選項' .  cc('(q)', 'N') . '：';
    ($cho = strtolower(trim(fgets(STDIN)))) || $cho = 'q';
  } while (!in_array($cho, ['1', '2', 'q']));
}

$cho === '1' && cho1();
$cho === '2' && cho2();

headerText('q');
$cho === 'q' && exit("\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n\n" . "  好的！期待您下次再使用，" . cc('掰掰', 'W') . "～  \n\n");
