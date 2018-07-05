<?php defined('MAZU') || exit('此檔案不允許讀取！');

Load::sysLib('SaveTool' . DIRECTORY_SEPARATOR . 'SaveTool.php');

class LocalSaveTool extends SaveTool {
  
  public static function create($bucket, $logFunc = null) {
    return new static($bucket, $logFunc);
  }

  public function put($filePath, $localPath) {
    if (!(is_file($filePath) && is_readable($filePath)))
      return $this->log('無法開啟檔案！', '檔案路徑：' . $filePath, '儲存路徑：' . $localPath, 'Bucket：' . $this->bucket);

    $path = pathinfo($tmp = $this->bucket . $localPath, PATHINFO_DIRNAME);

    if (!file_exists($path))
      umaskMkdir($path, 0777, true);

    if (!is_dir($path))
      return $this->log('資料夾不能存在！', '資料夾路徑：' . $path);

    if (!isReallyWritable($path))
      return $this->log('資料夾不能讀寫！', '資料夾路徑：' . $path);

    
    if (!@copy($filePath, $tmp))
      return $this->log('複製檔案失敗！', '檔案路徑：' . $tmp);

    @umaskChmod($tmp, 0777);

    return is_readable($tmp) ? true : $this->log('檔案不可讀取！', '檔案路徑：' . $tmp);
  }

  public function delete($path) {
    return @unlink($path) ? true : $this->log('清除檔案發生錯誤！', '檔案路徑：' . $path);
  }
}