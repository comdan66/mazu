<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Tag extends Model {
  // static $hasOne = [
  // ];
  
  // static $hasMany = [
  // ];

  // static $belongToOne = [
  // ];

  // static $belongToMany = [
  // ];

  // static $uploaders = [
  // ];

  // ->enable = Tag::ENABLE['YES'];

  const ENABLE_YES = '0';
  const ENABLE_NO  = 'no';

  const ENABLE = [
    self::ENABLE_YES => '啟用', 
    self::ENABLE_NO  => '停用'
  ];
}

