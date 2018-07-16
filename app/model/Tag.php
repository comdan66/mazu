<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends Model {
  // static $hasOne = [
  // ];
  
  static $hasMany = [
    'articleTags' => ['model' => 'ArticleTagMapping'],
  ];

  // static $belongToOne = [
  // ];

  // static $belongToMany = [
  // ];

  // static $uploaders = [
  // ];

  const ENABLE_YES = 'yes';
  const ENABLE_NO  = 'no';

  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用'
  ];
}

