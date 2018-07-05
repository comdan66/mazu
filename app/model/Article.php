<?php

namespace M;

class Article extends Model {
  // static $tableName ='devices';

  static $hasOne = [
  ];
  
  static $hasMany = [
    'tags'        => ['model' => 'Tag', 'by' => 'articleMappings'],
    'comments'    => ['model' => 'Comment'],

    'tagMappings' => ['model' => 'TagArticleMapping'],
  ];

  static $belongToOne = [
    // 'users' => ['model' => 'User', 'select' => 'name'],
  ];

  static $belongToMany = [
    'users' => ['model' => 'User', 'primaryKey' => 'name'],
  ];

  static $uploaders = [
    // 'cover'  => 'ArticleCoverFileUploader',
    'cover' => 'ArticleCoverImageUploader',
  ];
}

class ArticleCoverImageUploader extends ImageUploader {
  public function versions() {
    return [
      '' => [],

      'w100' => [
        'resize' => [100, 100, 'width']],

      // 'w1440' => [
      //   'resize' => [1440, 1440, 'width']],

      // 'c1200x630' => [
      //   'adaptiveResizeQuadrant' => [1200, 630, 't'],
      //   'resize' => [100, 100]],
    ];
  }
}

// class ArticleCoverFileUploader extends FileUploader {

// }
// // // 1/0