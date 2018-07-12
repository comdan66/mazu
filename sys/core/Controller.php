<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Controller {
  private $constructError = null;

  public function constructError() {
    return $this->constructError;
  }

  protected function setConstructError($error) {
    return $this->constructError = $error;
  }
}

spl_autoload_register(function($className) {
  if (!preg_match("/Controller$/", $className))
    return false;

  Load::core($className . '.php') && class_exists($className) || gg('找不到名稱為「' . $className . '」的 Controller 物件！');
  return true;
});

