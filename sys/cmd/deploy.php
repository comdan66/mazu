<?php

namespace Deployer;

defined('MAZU') || exit('此檔案不允許讀取！');

system('clear');

inventory('hosts.yml');

task('deploy:start', function() {
  echo "\n";

  $path = get('path', [null]);
  is_array($path) && exit(cc(' 錯誤！', 'r') . " 請您先設定" . cc('部署路徑', 'W') . "(" . cc('path', 'N') . ")！" . "\n" . "\n");

  $name = get('name', '');
  $name && $name = '「' . cc($name, 'W') . '」';

  echo cc(" 坐好囉！", 'Y') . "我們即將開始幫您部署" . $name . "！\n";
})->shallow()->setPrivate();

task('deploy:check', function() {
  $path = rtrim(get('path'), '/');

  echo "\n";
  echo " " . cc('【檢查變數】', 'W') . "\n";

  echo cc("   ➤ ", 'R') . "檢查路徑 " . cc($path, 'W') . " 是否正確" . cc(' ─ ', 'N');
  if (!test('[ -d ' . $path . ' ]')) {
    echo cc("錯誤", 'r') . "\n";
    throw new \RuntimeException("部署路徑 " . $path . " 不存在！");
  }
  echo cc("正確", 'g') . "\n";

  echo cc("   ➤ ", 'R') . "檢查路徑 " . cc($path . '/sys', 'W') . " 是否正確" . cc(' ─ ', 'N');
  if (!test('[ -d ' . $path . '/sys ]')) {
    echo cc("錯誤", 'r') . "\n";
    throw new \RuntimeException("部署路徑 " . $path . '/sys' . " 不存在！");
  }
  echo cc("正確", 'g') . "\n";

  echo cc("   ➤ ", 'R') . "檢查專案是否有 Git 管理" . cc(' ─ ', 'N');
  if (!test('[ -d ' . $path . '/.git ]')) {
    echo cc("沒有", 'r') . "\n";
    throw new \RuntimeException("專案內沒有 .git 目錄！");
  }
  echo cc("有的", 'g') . "\n";
})->shallow()->setPrivate();

task('deploy:gitPull', function() {
  $path = rtrim(get('path'), '/');

  $git = locateBinaryPath('git');
  $remote = get('remote', 'origin');
  $branch = get('branch', 'master');
  $cmd = "pull" . ($remote ? " " . $remote : "") . ($branch ? " " . $branch : "");

  echo "\n";
  echo " " . cc('【Git Pull】', 'W') . "\n";

  echo cc("   ➤ ", 'R') . "進入專案目錄：" . cc($path, 'W') . cc(' ─ ', 'N');
  cd($path);
  echo cc("成功", 'g') . "\n";
  
  echo cc("   ➤ ", 'R') . "執行指令：" . cc('git ' . $cmd, 'W') . cc(' ─ ', 'N');
  try {
    run("$git " . $cmd);
  } catch (\Throwable $exception) {
    echo cc("失敗", 'r') . "\n";
    throw new \RuntimeException("執行 git " . $cmd . " 指令失敗！");
  }
  echo cc("成功", 'g') . "\n";
})->shallow()->setPrivate();

task('deploy:Migration', function() {
  $path = rtrim(get('path'), '/');
  $php = locateBinaryPath('php');
  
  echo "\n";
  echo " " . cc('【Migration】', 'W') . "\n";

  echo cc("   ➤ ", 'R') . "進入專案目錄：" . cc($path . '/sys', 'W') . cc(' ─ ', 'N');
  cd($path . '/sys');
  echo cc("成功", 'g') . "\n";

  echo cc("   ➤ ", 'R') . "檢查專案是否有 Migration 執行檔" . cc(' ─ ', 'N');
  if (!test('[ -f ' . $path . '/sys/migration ]')) {
    echo cc("沒有", 'r') . "\n";
    throw new \RuntimeException("專案沒有 Migration 執行檔！");
  }
  echo cc("有的", 'g') . "\n";

  echo cc("   ➤ ", 'R') . "執行 Migration 指令：" . cc('php migration new', 'W') . cc(' ─ ', 'N');
  $res = run("$php migration new deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'], $result['now'])) {
    echo cc("失敗", 'r') . "\n";
    echo "     " . cc('➤', 'B') . " 錯誤原因：" . cc('回傳結構有誤！', 'W') . "\n";
    echo "     " . cc('➤', 'B') . " 回傳結果：" . cc($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cc("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cc('➤', 'B') . ' ' . $title . '：' . cc($msg, 'W') . "\n";
    echo "     " . cc('➤', 'B') . ' ' . '目前版本' . '：' . cc($result['now'], 'W') . "\n";
    throw new \RuntimeException("執行 Migration 失敗！");
  }
  echo cc("成功", 'g') . "\n";
  echo cc("   ➤ ", 'R') . "目前 Migration 版本：第 " . cc($result['now'], 'W') . " 版". "\n";
})->shallow()->setPrivate();

task('deploy:Clean', function() {
  $path = rtrim(get('path'), '/');
  $php = locateBinaryPath('php');

  echo "\n";
  echo " " . cc('【清除 Cache】', 'W') . "\n";
  
  echo cc("   ➤ ", 'R') . "進入專案目錄：" . cc($path . '/sys', 'W') . cc(' ─ ', 'N');
  cd($path . '/sys');
  echo cc("成功", 'g') . "\n";

  echo cc("   ➤ ", 'R') . "檢查專案是否有 Clean 執行檔" . cc(' ─ ', 'N');
  if (!test('[ -f ' . $path . '/sys/clean ]')) {
    echo cc("沒有", 'r') . "\n";
    throw new \RuntimeException("專案沒有 Clean 執行檔！");
  }
  echo cc("有的", 'g') . "\n";


  echo cc("   ➤ ", 'R') . "清空 " . cc("Cache 目錄", 'p') . "，執行指令：" . cc('php clean cache', 'W') . cc(' ─ ', 'N');
  $res = run("$php clean cache deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'])) {
    echo cc("失敗", 'r') . "\n";
    echo "     " . cc('➤', 'B') . " 錯誤原因：" . cc('回傳結構有誤！', 'W') . "\n";
    echo "     " . cc('➤', 'B') . " 回傳結果：" . cc($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cc("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cc('➤', 'B') . ' ' . $title . '：' . (is_array($msg) ? "\n" . implode("\n", array_map(function($t) { return "       " . cc('•', 'N') . " " . $t; }, $msg)) : cc($msg, 'W'))  . "\n";
    throw new \RuntimeException("執行清除 Cache 目錄失敗！");
  }
  echo cc("成功", 'g') . "\n";


  echo cc("   ➤ ", 'R') . "清空 " . cc("Tmp 目錄", 'p') . "，執行指令：" . cc('php clean tmp', 'W') . cc(' ─ ', 'N');
  $res = run("$php clean tmp deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'])) {
    echo cc("失敗", 'r') . "\n";
    echo "     " . cc('➤', 'B') . " 錯誤原因：" . cc('回傳結構有誤！', 'W') . "\n";
    echo "     " . cc('➤', 'B') . " 回傳結果：" . cc($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cc("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cc('➤', 'B') . ' ' . $title . '：' . (is_array($msg) ? "\n" . implode("\n", array_map(function($t) { return "       " . cc('•', 'N') . " " . $t; }, $msg)) : cc($msg, 'W'))  . "\n";
    throw new \RuntimeException("執行清除 Tmp 目錄失敗！");
  }
  echo cc("成功", 'g') . "\n";


  echo cc("   ➤ ", 'R') . "清空 " . cc("Session 目錄", 'p') . "，執行指令：" . cc('php clean session', 'W') . cc(' ─ ', 'N');
  $res = run("$php clean session deploy");
  $result = json_decode($res, true);

  if (!isset($result['status'], $result['msgs'])) {
    echo cc("失敗", 'r') . "\n";
    echo "     " . cc('➤', 'B') . " 錯誤原因：" . cc('回傳結構有誤！', 'W') . "\n";
    echo "     " . cc('➤', 'B') . " 回傳結果：" . cc($res, 'W') . "\n";
    throw new \RuntimeException("回傳結構有誤！");
  }

  if ($result['status'] !== 1) {
    echo cc("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cc('➤', 'B') . ' ' . $title . '：' . (is_array($msg) ? "\n" . implode("\n", array_map(function($t) { return "       " . cc('•', 'N') . " " . $t; }, $msg)) : cc($msg, 'W'))  . "\n";
    throw new \RuntimeException("執行清除 Session 目錄失敗！");
  }
  echo cc("成功", 'g') . "\n";
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
  $name && $name = '' . cc($name, 'W') . ' ';

  echo "\n";
  echo " " . cc('太棒惹！', 'Y') . '' . cc($name, 'W') . '已經部署成功囉！' . "\n";
  echo " " . cc(' ➤ 目前已經是最新版囉！趕緊打開網頁看看吧！', 'N') . "\n";
  echo "\n";
})->shallow()->setPrivate();

task('deploy:failed', function() {
  echo "\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n";
  echo "\n";
  echo cc(" 發生錯誤啦！！", 'r') . ' 以下是錯誤原因，請再確認一下吧！';
  echo "\n";
  echo "\n" . cc(str_repeat('═', CLI_LEN), 'N') . "\n";
})->shallow()->setPrivate();

after('deploy', 'deploy:success');
fail('deploy', 'deploy:failed');
