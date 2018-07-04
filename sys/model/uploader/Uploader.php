<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

abstract class Uploader {
  const DRIVER_S3    = 's3';
  const DRIVER_LOCAL = 'local';

  protected static $tmpDir = null;
  protected static $baseUrl = '/';
  
  protected static $driver = null;
  protected static $baseDirs = [];
  protected static $s3Bucket = null;

  private static $errorFunc = null;
  private static $logFunc = null;


  public static function setLogFunc($logFunc) {
    is_callable($logFunc) && self::$logFunc = $logFunc;
  }
  
  public static function log() {
    ($func = self::$logFunc) && call_user_func_array($func, func_get_args());
    return false;
  }

  public static function setErrorFunc($errorFunc) {
    is_callable($errorFunc) && self::$errorFunc = self::$errorFunc = $errorFunc;
  }

  public static function error($error) {
    $args = func_get_args();
    ($func = self::$errorFunc) && call_user_func_array($func, [array_shift($args), 500, ['msgs' => $args]]) || exit(implode(', ', $args));
  }

  public static function setTmpDir($tmpDir) {
    \isReallyWritable($tmpDir) && self::$tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }
  
  public static function setBaseUrl($baseUrl) {
    is_string($baseUrl) && self::$baseUrl = rtrim($baseUrl, '/') . '/';
  }

  private static function setDriver($driver) {
    in_array($driver, [self::DRIVER_S3, self::DRIVER_LOCAL]) && self::$driver = $driver;
  }

  public static function setBaseDirs($baseDirs) {
    is_array($baseDirs) && $baseDirs && self::$baseDirs = $baseDirs;
  }

  public static function useDriverLocal($baseDirs) {
    self::setDriver(self::DRIVER_LOCAL);
    self::setBaseDirs($baseDirs);
  }

  public static function setS3Bucket($s3Bucket) {
    is_string($s3Bucket) && $s3Bucket && self::$s3Bucket = trim($s3Bucket, '/');
  }

  public static function useDriverS3($s3Bucket, $baseDirs) {
    self::setDriver(self::DRIVER_S3);
    self::setS3Bucket($s3Bucket);
    self::setBaseDirs($baseDirs);
  }

  public static function bind($orm, $column) {
    $class = get_called_class();
    return new $class($orm, $column);
  }

  protected static function tmpDirNotWritable() {
    return self::$tmpDir === null || !\isReallyWritable(self::$tmpDir);
  }











  protected $orm = null;
  protected $column = null;
  protected $value = null;

  public function __construct($orm, $column) {
    $attrs = $orm->attrs();

    self::$driver !== null                                              || Uploader::error('尚未設定 Uploader Driver 類型！');
    self::$driver == Uploader::DRIVER_LOCAL || class_exists('S3')       || Uploader::error('Uploader Driver 為 S3，未先初始 S3 物件！');
    self::$driver == Uploader::DRIVER_LOCAL || self::$s3Bucket !== null || Uploader::error('Uploader Driver 為 S3，未指定 Bucket！');
    
    self::$baseDirs || Uploader::error('Uploader 未指定 baseDirs！');
    // ===============

    $this->orm = $orm;
    $this->column = $column;
    $this->value = $orm->$column;
    $orm->$column = $this;
  }

  protected function uniqueColumn() {
    return 'id';
  }

  public function __toString() {
    return (string)$this->value;
  }

  public function url($key = '') {
    return ($path = $this->pathDirs($key)) ? self::$baseUrl . implode('/', $path) : $this->d4Url();
  }

  public function pathDirs($fileName = '') {
    return $fileName ? array_merge(self::$baseDirs, $this->getSaveDirs(), [$fileName]) : [];
  }

  public function getSaveDirs() {
    array_key_exists($this->uniqueColumn(), $attrs) || Uploader::error('此物件 「' . get_class($orm) . '」 沒有 「' . $this->uniqueColumn() . '」 欄位！');
    return is_numeric($id = $this->orm->attrs($this->uniqueColumn(), 0)) ? array_merge([$this->orm->getTableName(), $this->column], str_split(sprintf('%08s', dechex($id)), 2)) : [$this->orm->getTableName(), $this->column];
  }

  protected function d4Url() {
    return '';
  }


  public function put($fileInfo) {
    if (!($fileInfo && (is_array($fileInfo) || (is_string($fileInfo) && file_exists($fileInfo)))))
      return self::log('[Uploader] put 格式有誤(1)。');

    if ($isUseMoveUploadedFile = is_array($fileInfo)) {
      foreach (['name', 'tmp_name', 'type', 'error', 'size'] as $key)
        if (!array_key_exists($key, $fileInfo))
          return self::log('[Uploader] put 格式有誤(2)。');

      $name = $fileInfo['name'];
    } else {
      $name = basename($fileInfo);
      $fileInfo = ['name' => 'file', 'tmp_name' => $fileInfo, 'type' => '', 'error' => '', 'size' => '1'];
    }

    $name = preg_replace("/[^a-zA-Z0-9\\._-]/", "", $name);
    $format = ($format = pathinfo($name, PATHINFO_EXTENSION)) ? '.' . $format : '';
    $name = ($name = pathinfo($name, PATHINFO_FILENAME)) ? $name . $format : getRandomName() . $format;

    if (!$temp = $this->moveOriFile($fileInfo, $isUseMoveUploadedFile))
      return self::log('[Uploader] put 搬移至暫存資料夾時發生錯誤。');

    if (!$saveDirs = $this->verifySaveDirs())
      return self::log('[Uploader] put 確認儲存路徑發生錯誤。');

    if (!$result = $this->moveFileAndUploadColumn($temp, $saveDirs, $name))
      return self::log('[Uploader] put 搬移預設位置時發生錯誤。');

    return true;
  }

  private function moveOriFile($fileInfo, $isUseMoveUploadedFile) {
    if (self::tmpDirNotWritable())
      return self::log('Tmp 資料夾無法寫入！');

    $temp = self::$tmpDir . 'uploader_' . getRandomName();

    if ($isUseMoveUploadedFile)
      @move_uploaded_file($fileInfo['tmp_name'], $temp);
    else
      @rename($fileInfo['tmp_name'], $temp);

    umaskChmod($temp, 0777);

    if (!file_exists($temp))
      return self::log('[Uploader] moveOriFile 移動檔案失敗。Path：' . $temp);

    return $temp;
  }

  private function verifySaveDirs() {
    switch (self::$driver) {
      case 'local':

        if (!is_writable($path = implode(DIRECTORY_SEPARATOR, self::$baseDirs)))
          return self::log('[Uploader] verifySaveDirs 資料夾不能儲存。Path：' . $path);

        file_exists($tmp = implode(DIRECTORY_SEPARATOR, $path = array_merge(self::$baseDirs, $this->getSaveDirs()))) || umaskMkdir($tmp, 0777, true);

        if (!is_writable($tmp))
          return self::log('[Uploader] verifySaveDirs 資料夾不能儲存。Path：' . $tmp);

        return $path;
        break;

      case 's3':
        return array_merge(self::$baseDirs, $this->getSaveDirs());
        break;
    }
    return false;
  }
  
  protected function moveFileAndUploadColumn($temp, $saveDirs, $oriName) {
    switch (self::$driver) {
      case 'local':
        if (!@rename($temp, $path = implode(DIRECTORY_SEPARATOR, $saveDirs) . DIRECTORY_SEPARATOR . $oriName))
          return self::log('[Uploader] moveFileAndUploadColumn local rename 搬移預設位置時發生錯誤。Path：' . $path);
        break;

      case 's3':
        if (!\S3::putObject($temp, self::$s3Bucket, $uri = implode ('/', $saveDirs) . '/' . $oriName))
          return self::log('[Uploader] moveFileAndUploadColumn s3 putObject 丟至 S3 發生錯誤。Bucket：' . self::$s3Bucket . '，uri：' . $uri);

        @unlink($temp) || self::log('[Uploader] moveFileAndUploadColumn s3 移除舊資料錯誤。');
        break;

    }

    if (!$this->uploadColumnAndUpload(''))
      return self::log('[Uploader] moveFileAndUploadColumn uploadColumnAndUpload = "" 錯誤。');

    if (!$this->uploadColumnAndUpload($oriName))
      return self::log('[Uploader] moveFileAndUploadColumn uploadColumnAndUpload = ' . $oriName . ' 錯誤。');

    return true;
  }

  protected function uploadColumnAndUpload($value, $isSave = true) {
    $this->cleanOldFile ();
    return $isSave ? $this->uploadColumn($value) : true;
  }

  protected function cleanOldFile() {
    switch (self::$driver) {
      case 'local':
        if ($PathsDirs = $this->getPathsDirs())
          foreach ($PathsDirs as $pathDirs)
            if (is_writable($pathDir = implode(DIRECTORY_SEPARATOR, $pathDirs)))
              @unlink($pathDir) || self::log('[Uploader] cleanOldFile 清除檔案發生錯誤。Path：' . $pathDir);
        break;

      case 's3':
        if ($PathsDirs = $this->getPathsDirs())
          foreach ($PathsDirs as $pathDirs)
            \S3::deleteObject(self::$s3Bucket, $pathDir = implode('/', $pathDirs)) || self::log('[Uploader] cleanOldFile 清除檔案發生錯誤。Path：' . $pathDir);
        break;
    }

    return true;
  }

  public function getPathsDirs() {
    if (!(string)$this->value)
      return [];

    return [array_merge(self::$baseDirs, $this->getSaveDirs(), [(string)$this->value])];
  }

  protected function uploadColumn($value) {
    $column = $this->column;
    $this->orm->$column = $value;

    if (!$this->orm->save())
      return false;

    $this->value = $value;
    $this->orm->$column = $this;
    return true;
  }

  public function cleanAllFiles($isSave = true) {
    return $this->uploadColumnAndUpload('', $isSave);
  }

  public function putUrl($url) {
    if (self::tmpDirNotWritable())
      return self::log('Tmp 資料夾無法寫入！');

    $format = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $temp = downloadWebFile($url, self::$tmpDir . getRandomName() . ($format ? '.' . $format : ''));
    return $temp && $this->put($temp, false) ? file_exists($temp) ? @unlink($temp) : true : false;
  }
}