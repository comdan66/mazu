<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

abstract class Uploader {
  protected static $baseUrl = '/';
  protected static $dir = null;
  private static $tmpDir = null;

  private static $errorFunc = null;
  private static $logFunc = null;
  private static $saveTool = null;
  private static $thumbnail = null;


  public static function setLogFunc($logFunc) {
    is_callable($logFunc) && self::$logFunc = $logFunc;
  }

  public static function setErrorFunc($errorFunc) {
    is_callable($errorFunc) && self::$errorFunc = self::$errorFunc = $errorFunc;
  }
  
  protected static function log() {
    ($func = self::$logFunc) && call_user_func_array($func, func_get_args());
    return false;
  }

  protected static function error($error) {
    $args = func_get_args();
    ($func = self::$errorFunc) && call_user_func_array($func, [array_shift($args), 500, ['msgs' => $args]]) || exit(implode(', ', $args));
  }

  public static function setTmpDir($tmpDir) {
    \isReallyWritable($tmpDir) && self::$tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }
  
  public static function setBaseUrl($baseUrl) {
    is_string($baseUrl) && self::$baseUrl = rtrim($baseUrl, '/') . '/';
  }

  public static function setDir($dir) {
    is_string($dir) && ($dir = trim($dir, '/')) && self::$dir = $dir;
  }
  
  protected static function thumbnail($file) {
    is_callable(self::$thumbnail) && ($thumbnail = self::$thumbnail) && self::$thumbnail = $thumbnail($file);
    self::$thumbnail || self::error('尚未設定縮圖工具！');
    return self::$thumbnail;
  }

  public static function initThumbnail($thumbnail) {
    is_callable($thumbnail) && self::$thumbnail = $thumbnail;
  }

  protected static function saveTool() {
    self::$saveTool !== null || self::error('尚未設定儲存工具！');

    if (is_callable(self::$saveTool) && ($saveTool = self::$saveTool))
      if (!self::$saveTool = $saveTool())
        self::error('尚未設定儲存工具！');

    if (is_object(self::$saveTool))
      return self::$saveTool;
  }

  public static function initSaveTool($saveTool) {
    is_callable($saveTool) && self::$saveTool = $saveTool;
  }

  public static function bind($orm, $column) {
    $class = get_called_class();
    return new $class($orm, $column);
  }

  protected static function tmpDir() {
    self::$tmpDir !== null && \isReallyWritable(self::$tmpDir) || self::error('Uploader 尚未設定 tmp 目錄或無法寫入！');
    return self::$tmpDir;
  }

  protected static function dir() {
    self::$dir || self::error('Uploader 未指定 dir！');
    return self::$dir . '/';
  }

  protected $orm = null;
  protected $column = null;
  protected $value = null;

  public function __construct($orm, $column) {
    $attrs = $orm->attrs();

    $this->orm = $orm;
    $this->column = $column;
    $this->value  = $orm->$column;
    $orm->$column = $this;
  }

  protected function uniqueColumn() {
    return 'id';
  }

  public function __toString() {
    return (string)$this->value;
  }

  public function url($key = '') {
    return ($path = $this->path($key)) ? self::$baseUrl . $path : $this->d4Url();
  }

  public function path($fileName = '') {
    return $fileName ? self::dir() . $this->savePath() . $fileName : '';
  }

  public function savePath() {
    array_key_exists($this->uniqueColumn(), $this->orm->attrs()) || self::error('此物件 「' . get_class($orm) . '」 沒有 「' . $this->uniqueColumn() . '」 欄位！');
    $id = $this->orm->attrs($this->uniqueColumn(), 0);
    $tmp = $this->orm->getTableName() . '/' . $this->column . '/';
    return $tmp . (is_numeric($id)
                  ? implode('/', str_split(sprintf('%08s', dechex($id)), 2)) . '/'
                  : '');
  }

  protected function d4Url() {
    return '';
  }

  public function put($fileInfo) {
    if (!($fileInfo && (is_array($fileInfo) || (is_string($fileInfo) && file_exists($fileInfo)))))
      return self::log('上傳檔案格式有誤(1)！', '檔案：' . \dump($fileInfo));

    if ($isUseMoveUploadedFile = is_array($fileInfo)) {
      foreach (['name', 'tmp_name', 'type', 'error', 'size'] as $key)
        if (!array_key_exists($key, $fileInfo))
          return self::log('上傳檔案格式有誤(2)！', '檔案：' . \dump($fileInfo), '缺少 key：' . $key);

      $name = $fileInfo['name'];
    } else {
      $name = basename($fileInfo);
      $fileInfo = ['name' => 'file', 'tmp_name' => $fileInfo, 'type' => '', 'error' => '', 'size' => '1'];
    }

    $name = preg_replace("/[^a-zA-Z0-9\\._-]/", "", $name);
    $format = ($format = pathinfo($name, PATHINFO_EXTENSION)) ? '.' . $format : '';
    $name = ($name = pathinfo($name, PATHINFO_FILENAME)) ? $name . $format : getRandomName() . $format;

    if (!$tmp = $this->moveOriFile($fileInfo, $isUseMoveUploadedFile))
      return self::log('put 搬移至暫存資料夾時發生錯誤！', 'moveOriFile 失敗！');

    if (!$result = $this->moveFileAndUploadColumn($tmp, self::dir() . $this->savePath(), $name))
      return self::log('put 搬移預設位置時發生錯誤！', 'moveFileAndUploadColumn 失敗！');

    return true;
  }

  private function moveOriFile($fileInfo, $isUseMoveUploadedFile) {
    $tmpDir = self::tmpDir();

    $tmp = $tmpDir . 'uploader_' . getRandomName();

    if ($isUseMoveUploadedFile)
      @move_uploaded_file($fileInfo['tmp_name'], $tmp);
    else
      @rename($fileInfo['tmp_name'], $tmp);

    \umaskChmod($tmp, 0777);

    return file_exists($tmp) ? $tmp : self::log('moveOriFile 移動檔案失敗！', '檔案路徑：' . $tmp);
  }

  protected function moveFileAndUploadColumn($tmp, $path, $oriName) {
    if (!self::saveTool()->put($tmp, $path . $oriName))
      return self::log('Save Tool put 發生錯誤！', '檔案路徑：' . $tmp, '儲存路徑：' . $path . $oriName);

    @unlink($tmp) || self::log('移除舊資料錯誤！');

    if (!$this->uploadColumnAndUpload(''))
      return self::log('清空欄位值失敗！');

    if (!$this->uploadColumnAndUpload($oriName))
      return self::log('設定欄位值失敗！');

    return true;
  }

  protected function uploadColumnAndUpload($value, $isSave = true) {
    $this->cleanOldFile();
    return $isSave ? $this->uploadColumn($value) : true;
  }

  protected function cleanOldFile() {
    if ($paths = $this->paths())
      foreach ($paths as $path)
        if (!self::saveTool()->delete($path))
          self::log('清除檔案發生錯誤！', '檔案路徑：' . $path);
    
    return true;
  }

  public function paths() {
    if (!(string)$this->value)
      return [];

    return [self::dir() . $this->savePath() . $this->value];
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
    $tmpDir = self::tmpDir();
    $format = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $tmp = downloadWebFile($url, $tmpDir . getRandomName() . ($format ? '.' . $format : ''));
    return $tmp && $this->put($tmp, false) ? file_exists($tmp) ? @unlink($tmp) : true : false;
  }
}