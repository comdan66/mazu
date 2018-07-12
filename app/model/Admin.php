<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Admin extends Model {
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

  // private static $current = null;

  public static function current() {
    return \Session::getData('admin');
    // if (self::$current)
    //   return self::$current;

    // \Load::sysLib('Session.php') || gg('載入 Session 失敗！');
    // $token = \Session::getData('token');

    // return self::$current = $token ? Admin::one('token = ?', $token) : $token;
  }
}
