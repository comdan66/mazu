<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'up' => "CREATE TABLE `ArticleTagMapping` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `tagId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Tag ID',
    `articleId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Article ID',
    `updateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    `createAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

  'down' => "DROP TABLE `ArticleTagMapping`;",

  'at' => "2018-07-17 16:50:43"
];
