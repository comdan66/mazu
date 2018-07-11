<?php defined('MAZU') || exit('此檔案不允許讀取！');

abstract class SaveTool {
  protected $bucket = null;

  protected function __construct($bucket) {
    $this->bucket = $bucket;
  }


  protected function log() {
    call_user_func_array('Log::saveTool', func_get_args());
    return false;
  }

  abstract public function put($filePath, $localPath);
  abstract public function delete($path);
}