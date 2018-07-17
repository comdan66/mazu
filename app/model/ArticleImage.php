<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class ArticleImage extends Model {
  // static $hasOne = [];

  // static $hasMany = [];

  // static $belongToOne = [];

  // static $belongToMany = [];

  static $uploaders = [
    'pic' => 'ArticleImagePicImageUploader',
  ];

  public function putFiles($files) {
    foreach ($files as $key => $file)
      if (isset($this->$key) && $this->$key instanceof Uploader && !$this->$key->put($file))
        return false;
    return true;
  }
}

class ArticleImagePicImageUploader extends ImageUploader {
  public function versions() {
    return [
      'w100' => ['resize' => [100, 100, 'width']],
    ];
  }
}
