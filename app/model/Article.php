<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Article extends Model {
  // static $hasOne = [];

  static $hasMany = [
    'images' => ['model' => 'ArticleImage'],
    'tags' => ['model' => 'Tag', 'by' => 'articleTagMappings'],
    'articleTagMappings' => ['model' => 'ArticleTagMapping'],
  ];

  // static $belongToOne = [];

  // static $belongToMany = [];

  static $uploaders = [
    'cover' => 'ArticleCoverImageUploader',
  ];

  const ENABLE_YES = 'yes';
  const ENABLE_NO  = 'no';

  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用'
  ];

  public function delete() {
    foreach ($this->images as $image)
      if (!$image->delete())
        return false;

    return parent::delete();
  }

  public function putFiles($files) {
    foreach ($files as $key => $file)
      if (isset($this->$key) && $this->$key instanceof Uploader && !$this->$key->put($file))
        return false;
    return true;
  }
  
  public function minColumn($column, $length = 100) {
    if (!isset($this->$column))
      return '';

    return $length ? mb_strimwidth(strip_tags($this->$column), 0, $length, '…','UTF-8') : strip_tags($this->$column);
  }
}

class ArticleCoverImageUploader extends ImageUploader {
  public function versions() {
    return [
      'w100' => ['resize' => [100, 100, 'width']],
    ];
  }
}
