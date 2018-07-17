<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'up' => "INSERT INTO `AdminRole` (`id`, `adminId`, `role`)VALUES(1, 1, 'manager');",

  'down' => "DELETE FROM `AdminRole` WHERE `id`=1;",

  'at' => "2018-07-17 16:35:11"
];
