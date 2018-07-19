<?php

namespace Deployer;

define('CLI_LEN', 80);
define('CMD', 'deploy');
include '..' . DIRECTORY_SEPARATOR . 'index.php';

host('kerker.tw')
    ->set('projectName', 'Mazu!')
    ->user('ubuntu')
    ->port(22)
    ->set('deployPath', '~/www/mazu')
    ->set('remote', 'origin')
    ->set('branch', 'master');
    
task('deploy', function () {
  $deployPath = rtrim(get('deployPath'), '/');
  $projectName = get('projectName');

  echo "\n";
  echo cliColor(" 坐好囉！", 'Y') . "我們即將開始幫您部署「" . cliColor($projectName, 'W') . "」" . "\n";


  echo "\n";
  echo " " . cliColor('【檢查變數】', 'W') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查路徑 " . cliColor($deployPath, 'W') . " 是否正確" . cliColor(' ─ ', 'N');
  if (!test('[ -d ' . $deployPath . ' ]')) {
    echo cliColor("錯誤", 'r') . "\n";
    return ;
  }
  echo cliColor("正確", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查路徑 " . cliColor($deployPath . '/sys', 'W') . " 是否正確" . cliColor(' ─ ', 'N');
  if (!test('[ -d ' . $deployPath . '/sys ]')) {
    echo cliColor("錯誤", 'r') . "\n";
    return ;
  }
  echo cliColor("正確", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查專案是否有 Git 管理" . cliColor(' ─ ', 'N');
  if (!test('[ -d ' . $deployPath . '/.git ]')) {
    echo cliColor("沒有", 'r') . "\n";
    return ;
  }
  echo cliColor("有的", 'g') . "\n";


  echo "\n";
  echo " " . cliColor('【Git Pull】', 'W') . "\n";

  $git = locateBinaryPath('git');
  $php = locateBinaryPath('php');
  $remote = get('remote', 'origin');
  $branch = get('branch', 'master');
  $cmd = "pull" . ($remote ? " " . $remote : "") . ($branch ? " " . $branch : "");

  echo cliColor("   ➤ ", 'R') . "進入專案目錄：" . cliColor($deployPath, 'W') . cliColor(' ─ ', 'N');
  cd($deployPath);
  echo cliColor("成功", 'g') . "\n";
  
  echo cliColor("   ➤ ", 'R') . "執行指令：" . cliColor('git ' . $cmd, 'W') . cliColor(' ─ ', 'N');
  try {
    run("$git " . $cmd);
  } catch (\Throwable $exception) {
    echo cliColor("失敗", 'r') . "\n";
    return ;
  }
  echo cliColor("成功", 'g') . "\n";


  echo "\n";
  echo " " . cliColor('【Migration】', 'W') . "\n";

  echo cliColor("   ➤ ", 'R') . "進入專案目錄：" . cliColor($deployPath . '/sys', 'W') . cliColor(' ─ ', 'N');
  cd($deployPath . '/sys');
  echo cliColor("成功", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查專案是否有 Migration 執行檔" . cliColor(' ─ ', 'N');
  if (!test('[ -f ' . $deployPath . '/sys/migration ]')) {
    echo cliColor("沒有", 'r') . "\n";
    return ;
  }
  echo cliColor("有的", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "執行 Migration 指令：" . cliColor('php migration new', 'W') . cliColor(' ─ ', 'N');
  $result = run("$php migration new");
  $result = json_decode($result, true);

  if (!isset($result['status'], $result['msgs'], $result['now'])) {
    echo cliColor("失敗", 'r') . "\n";
    echo "     " . cliColor('➤', 'B') . " 錯誤原因：" . cliColor('回傳結構有誤！', 'W') . "\n";
    return ;
  }

  if ($result['status'] !== 1) {
    echo cliColor("失敗", 'r') . "\n";
    foreach ($result['msgs'] as $title => $msg)
      echo "     " . cliColor('➤', 'B') . ' ' . $title . '：' . cliColor($msg, 'W') . "\n";
    echo "     " . cliColor('➤', 'B') . ' ' . '目前版本' . '：' . cliColor($result['now'], 'W') . "\n";
    return ;
  }
  echo cliColor("成功", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "目前 Migration 版本：第 " . cliColor($result['now'], 'W') . " 版". "\n";

  echo "\n";
  echo " " . cliColor('【清除 Cache】', 'W') . "\n";
  
  echo cliColor("   ➤ ", 'R') . "進入專案目錄：" . cliColor($deployPath . '/sys', 'W') . cliColor(' ─ ', 'N');
  cd($deployPath . '/sys');
  echo cliColor("成功", 'g') . "\n";

  echo cliColor("   ➤ ", 'R') . "檢查專案是否有 Clean 執行檔" . cliColor(' ─ ', 'N');
  // if (!test('[ -f ' . $deployPath . '/sys/clean ]')) {
  //   echo cliColor("沒有", 'r') . "\n";
  //   return ;
  // }
  echo cliColor("有的", 'g') . "\n";


  echo "\n";
  echo " " . cliColor('太棒惹！', 'Y') . '' . cliColor($projectName, 'W') . ' 已經部署成功囉！' . "\n";
  echo " " . cliColor(' ➤ 目前已經是最新版囉！趕緊打開網頁看看吧！', 'N') . "\n";

  echo "\n";
})->shallow();
