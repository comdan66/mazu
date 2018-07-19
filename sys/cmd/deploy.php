<?php

namespace Deployer;

defined('MAZU') || exit('此檔案不允許讀取！');

system('clear');

inventory('hosts.yml');

task('deploy:start', function() {
  echo "\n";

  $path = get('path', [null]);
  is_array($path) && exit(cliColor(' 錯誤！', 'r') . " 請您先設定" . cliColor('部署路徑', 'W') . "(" . cliColor('path', 'N') . ")！" . "\n" . "\n");

  $name = get('name', '');
  $name && $name = '「' . cliColor($name, 'W') . '」';

  echo cliColor(" 坐好囉！", 'Y') . "我們即將開始幫您部署" . $name . "！\n";
})->shallow()->setPrivate();

task('deploy:check', function() {
  $path = rtrim(get('path'), '/');

  echo "\n";
  echo " " . cliColor('【檢查變數】', 'W') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查路徑 " . cliColor($path, 'W') . " 是否正確" . cliColor(' ─ ', 'N');
  if (!test('[ -d ' . $path . ' ]')) {
    echo cliColor("錯誤", 'r') . "\n";
    throw new \RuntimeException("部署路徑 " . $path . " 不存在！");
  }
  echo cliColor("正確", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查路徑 " . cliColor($path . '/sys', 'W') . " 是否正確" . cliColor(' ─ ', 'N');
  if (!test('[ -d ' . $path . '/sys ]')) {
    echo cliColor("錯誤", 'r') . "\n";
    throw new \RuntimeException("部署路徑 " . $path . '/sys' . " 不存在！");
  }
  echo cliColor("正確", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查專案是否有 Git 管理" . cliColor(' ─ ', 'N');
  if (!test('[ -d ' . $path . '/.git ]')) {
    echo cliColor("沒有", 'r') . "\n";
    throw new \RuntimeException("專案內沒有 .git 目錄！");
  }
  echo cliColor("有的", 'g') . "\n";
})->shallow()->setPrivate();

task('deploy:gitPull', function() {
  $path = rtrim(get('path'), '/');

  $git = locateBinaryPath('git');
  $remote = get('remote', 'origin');
  $branch = get('branch', 'master');
  $cmd = "pull" . ($remote ? " " . $remote : "") . ($branch ? " " . $branch : "");

  echo "\n";
  echo " " . cliColor('【Git Pull】', 'W') . "\n";

  echo cliColor("   ➤ ", 'R') . "進入專案目錄：" . cliColor($path, 'W') . cliColor(' ─ ', 'N');
  cd($path);
  echo cliColor("成功", 'g') . "\n";
  
  echo cliColor("   ➤ ", 'R') . "執行指令：" . cliColor('git ' . $cmd, 'W') . cliColor(' ─ ', 'N');
  try {
    run("$git " . $cmd);
  } catch (\Throwable $exception) {
    echo cliColor("失敗", 'r') . "\n";
    throw new \RuntimeException("執行 git " . $cmd . " 指令失敗！");
  }
  echo cliColor("成功", 'g') . "\n";
})->shallow()->setPrivate();

task('deploy:Migration', function() {
  $path = rtrim(get('path'), '/');
  $php = locateBinaryPath('php');
  
  echo "\n";
  echo " " . cliColor('【Migration】', 'W') . "\n";

  echo cliColor("   ➤ ", 'R') . "進入專案目錄：" . cliColor($path . '/sys', 'W') . cliColor(' ─ ', 'N');
  cd($path . '/sys');
  echo cliColor("成功", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查專案是否有 Migration 執行檔" . cliColor(' ─ ', 'N');
  if (!test('[ -f ' . $path . '/sys/migration ]')) {
    echo cliColor("沒有", 'r') . "\n";
    throw new \RuntimeException("專案沒有 Migration 執行檔！");
  }
  echo cliColor("有的", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "執行 Migration 指令：" . cliColor('php migration new', 'W') . cliColor(' ─ ', 'N');
  $res = run("$php migration new deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'], $result['now'])) {
    echo cliColor("失敗", 'r') . "\n";
    echo "     " . cliColor('➤', 'B') . " 錯誤原因：" . cliColor('回傳結構有誤！', 'W') . "\n";
    echo "     " . cliColor('➤', 'B') . " 回傳結果：" . cliColor($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cliColor("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cliColor('➤', 'B') . ' ' . $title . '：' . cliColor($msg, 'W') . "\n";
    echo "     " . cliColor('➤', 'B') . ' ' . '目前版本' . '：' . cliColor($result['now'], 'W') . "\n";
    throw new \RuntimeException("執行 Migration 失敗！");
  }
  echo cliColor("成功", 'g') . "\n";
  echo cliColor("   ➤ ", 'R') . "目前 Migration 版本：第 " . cliColor($result['now'], 'W') . " 版". "\n";
})->shallow()->setPrivate();

task('deploy:Clean', function() {
  $path = rtrim(get('path'), '/');
  $php = locateBinaryPath('php');

  echo "\n";
  echo " " . cliColor('【清除 Cache】', 'W') . "\n";
  
  echo cliColor("   ➤ ", 'R') . "進入專案目錄：" . cliColor($path . '/sys', 'W') . cliColor(' ─ ', 'N');
  cd($path . '/sys');
  echo cliColor("成功", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查專案是否有 Clean 執行檔" . cliColor(' ─ ', 'N');
  if (!test('[ -f ' . $path . '/sys/clean ]')) {
    echo cliColor("沒有", 'r') . "\n";
    throw new \RuntimeException("專案沒有 Clean 執行檔！");
  }
  echo cliColor("有的", 'g') . "\n";


  echo cliColor("   ➤ ", 'R') . "清空 " . cliColor("Cache 目錄", 'p') . "，執行指令：" . cliColor('php clean cache', 'W') . cliColor(' ─ ', 'N');
  $res = run("$php clean cache deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'])) {
    echo cliColor("失敗", 'r') . "\n";
    echo "     " . cliColor('➤', 'B') . " 錯誤原因：" . cliColor('回傳結構有誤！', 'W') . "\n";
    echo "     " . cliColor('➤', 'B') . " 回傳結果：" . cliColor($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cliColor("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cliColor('➤', 'B') . ' ' . $title . '：' . (is_array($msg) ? "\n" . implode("\n", array_map(function($t) { return "       " . cliColor('•', 'N') . " " . $t; }, $msg)) : cliColor($msg, 'W'))  . "\n";
    throw new \RuntimeException("執行清除 Cache 目錄失敗！");
  }
  echo cliColor("成功", 'g') . "\n";


  echo cliColor("   ➤ ", 'R') . "清空 " . cliColor("Tmp 目錄", 'p') . "，執行指令：" . cliColor('php clean tmp', 'W') . cliColor(' ─ ', 'N');
  $res = run("$php clean tmp deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'])) {
    echo cliColor("失敗", 'r') . "\n";
    echo "     " . cliColor('➤', 'B') . " 錯誤原因：" . cliColor('回傳結構有誤！', 'W') . "\n";
    echo "     " . cliColor('➤', 'B') . " 回傳結果：" . cliColor($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cliColor("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cliColor('➤', 'B') . ' ' . $title . '：' . (is_array($msg) ? "\n" . implode("\n", array_map(function($t) { return "       " . cliColor('•', 'N') . " " . $t; }, $msg)) : cliColor($msg, 'W'))  . "\n";
    throw new \RuntimeException("執行清除 Tmp 目錄失敗！");
  }
  echo cliColor("成功", 'g') . "\n";


  echo cliColor("   ➤ ", 'R') . "清空 " . cliColor("Session 目錄", 'p') . "，執行指令：" . cliColor('php clean session', 'W') . cliColor(' ─ ', 'N');
  $res = run("$php clean session deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'])) {
    echo cliColor("失敗", 'r') . "\n";
    echo "     " . cliColor('➤', 'B') . " 錯誤原因：" . cliColor('回傳結構有誤！', 'W') . "\n";
    echo "     " . cliColor('➤', 'B') . " 回傳結果：" . cliColor($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cliColor("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cliColor('➤', 'B') . ' ' . $title . '：' . (is_array($msg) ? "\n" . implode("\n", array_map(function($t) { return "       " . cliColor('•', 'N') . " " . $t; }, $msg)) : cliColor($msg, 'W'))  . "\n";
    throw new \RuntimeException("執行清除 Session 目錄失敗！");
  }
  echo cliColor("成功", 'g') . "\n";
})->shallow()->setPrivate();

task('deploy', [
  'deploy:start',
  'deploy:check',
  'deploy:gitPull',
  'deploy:Migration',
  'deploy:Clean',
])->shallow();

task('deploy:success', function() {
  $name = get('name', '');
  $name && $name = '' . cliColor($name, 'W') . ' ';

  echo "\n";
  echo " " . cliColor('太棒惹！', 'Y') . '' . cliColor($name, 'W') . '已經部署成功囉！' . "\n";
  echo " " . cliColor(' ➤ 目前已經是最新版囉！趕緊打開網頁看看吧！', 'N') . "\n";
  echo "\n";
})->shallow()->setPrivate();

task('deploy:failed', function() {
  echo "\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n";
  echo "\n";
  echo cliColor(" 發生錯誤啦！！", 'r') . ' 以下是錯誤原因，請再確認一下吧！';
  echo "\n";
  echo "\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n";
})->shallow()->setPrivate();

after('deploy', 'deploy:success');
fail('deploy', 'deploy:failed');
