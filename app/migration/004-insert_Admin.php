<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'up' => "INSERT INTO `Admin` (`id`, `name`, `account`, `password`)VALUES(1, 'OA', 'oa', '$2y$10$7hEPEiJONB/r7ZFZq4d7Wuvn92cRXZwK52vQUDzzyPd7a2grCMBya');",

  'down' => "DELETE FROM `Admin` WHERE `id`=1;",

  'at' => "2018-07-17 16:35:11"
];
