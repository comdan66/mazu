<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends Model {
  // static $hasOne = [];

  static $hasMany = [
    'articles' => ['model' => 'Article', 'by' => 'articleTagMappings'],
    'articleTagMappings' => ['model' => 'ArticleTagMapping'],
  ];

  // static $belongToOne = [];

  // static $belongToMany = [];

  // static $uploaders = [];

  const ENABLE_YES = 'yes';
  const ENABLE_NO  = 'no';

  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用'
  ];

  public function delete() {
    foreach ($this->articleTagMappings as $articleTag)
      if (!$articleTag->delete())
        return false;

    return parent::delete();
  }
}
