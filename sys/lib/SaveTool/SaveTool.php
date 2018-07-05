<?php defined('MAZU') || exit('此檔案不允許讀取！');

abstract class SaveTool {
  protected $bucket = null;
  private $logFunc = null;

  protected function __construct($bucket, $logFunc = null) {
    $this->bucket = $bucket;
    $this->setLogFunc($logFunc);
  }

  protected function setLogFunc($logFunc) {
    is_callable($logFunc) && $this->logFunc = $logFunc;
  }

  protected function log() {
    ($func = $this->logFunc) && call_user_func_array($func, func_get_args());
    return false;
  }

  abstract public function put($filePath, $localPath);
  abstract public function delete($path);
}