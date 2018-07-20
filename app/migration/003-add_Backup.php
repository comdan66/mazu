<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'up' => "CREATE TABLE `Backup` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `file` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '檔案',
    `size` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '檔案大小(Byte)',
    `type` enum('db', 'info', 'error', 'warning', 'model', 'uploader', 'saveTool', 'thumbnail', 'benchmark', 'query', 'other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT '類型',
    `status` enum('failure', 'success') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'failure' COMMENT '狀態',
    `unwatch` enum('yes', 'no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no' COMMENT '已讀',
    `updateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    `createAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

  'down' => "DROP TABLE `Backup`;",

  'at' => "2018-07-17 16:35:11"
];
