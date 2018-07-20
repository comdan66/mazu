<?php defined('MAZU') || exit('此檔案不允許讀取！');

// API
Router::dir('cli', function() {
  Router::cli('backup/db')->controller('Backup@db');
  Router::cli('backup/logs/(beforeDay:num)')->controller('Backup@logs');
  // Router::cli('x')->controller('Backup@x');
});
