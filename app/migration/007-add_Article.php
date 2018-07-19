<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'up' => "CREATE TABLE `Article` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `cover` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面',
    `title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '標題',
    `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '內容',
    `enable` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no' COMMENT '是否啟用',
    `updateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    `createAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

  'down' => "DROP TABLE `Article`;",

  'at' => "2018-07-17 16:46:56"
];
