<?php

namespace _M;

defined('MAZU') || exit('此檔案不允許讀取！');

class DateTime {
  private $format;
  private $datetime;

  public function __construct($str, $type) {
    $this->format = $type == 'datetime' ? Config::FORMAT_DATETIME : Config::FORMAT_DATE;
    $this->datetime = \DateTime::createFromFormat($this->format, $str);
  }

  public static function createByString($str, $type) {
    return new static($str, $type);
  }

  public function isFormat() {
    return !($this->datetime === false);
  }

  public function timestamp() {
    return $this->isFormat() ? $this->datetime->getTimestamp() : null;
  }

  // U -> timestamp, 'c' -> ISO 8601 date(2004-02-12T15:19:21+00:00)
  // http://php.net/manual/en/function.date.php
  public function format($format = null, $d4 = null) {
    return $this->isFormat() ? $this->datetime->format($format === null ? $this->format : $format) : $d4;
  }

  public function __toString() {
    return $this->format(null, '');
  }
}