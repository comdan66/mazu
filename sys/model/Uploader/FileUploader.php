<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

abstract class FileUploader extends Uploader {
  public function url($url ='') {
    return parent::url('');
  }

  public function path($key = null) {
    return parent::path((string)$this->value);
  }

  public function link($text, $attrs = []) { // $attrs = array ('class' => 'i')
    return ($url = ($url = $this->url()) ? $url : '') ? '<a href="' . $url . '"' . ($attrs ? ' ' . implode(' ', array_map(function($key, $value) { return $key . '="' . $value . '"'; }, array_keys($attrs), $attrs)) : '') . '>' . $text . '</a>' : '';
  }
}