<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Admin extends Model {
  // static $hasOne = [];

  static $hasMany = [
    'roles' => ['model' => 'AdminRole'],
  ];

  // static $belongToOne = [];

  // static $belongToMany = [];

  // static $uploaders = [];

  public static function current() {
    return \Session::getData('admin');
  }
}
