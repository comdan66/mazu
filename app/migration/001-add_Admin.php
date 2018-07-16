<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'up' => "CREATE TABLE `Admin` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名稱',
      `account` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '帳號',
      `password` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密碼',
      `updateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
      `createAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

  'down' => "DROP TABLE `Admin`;",

  'at' => "2018-04-16 11:48:05",
];