<?php defined('MAZU') || exit('此檔案不允許讀取！');

Load::sysLib('SaveTool' . DIRECTORY_SEPARATOR . 'SaveTool.php');
Load::sysLib('S3.php');

class S3SaveTool extends SaveTool {
  private $s3 = null;

  public function __construct($bucket, $accessKey, $secretKey, $logFunc = null) {
    parent::__construct($bucket, $logFunc);

    $this->s3 = new S3($accessKey, $secretKey);
  }

  public static function create($bucket, $accessKey, $secretKey, $logFunc = null) {
    return new static($bucket, $accessKey, $secretKey, $logFunc);
  }

  public function put($filePath, $localPath) {
    return $this->s3->putObject($filePath, $this->bucket, $localPath) ? true : $this->log('上傳 S3 失敗！', '檔案路徑：' . $filePath, '儲存路徑：' . $localPath, 'Bucket：' . $this->bucket);
  }
  public function delete($path) {
    return $this->s3->deleteObject($this->bucket, $path) ? true : $this->log('刪除 S3 檔案失敗', '檔案路徑：' . $path, 'Bucket：' . $this->bucket);
  }
}