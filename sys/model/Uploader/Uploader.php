<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

abstract class Uploader {
  protected static $baseUrl = '/';
  protected static $baseDirs = [];
  private static $tmpDir = null;

  private static $errorFunc = null;
  private static $logFunc = null;
  private static $saveTool = null;
  private static $thumbnail = null;

  protected static function thumbnail($file) {
    is_callable(self::$thumbnail) && ($thumbnail = self::$thumbnail) && self::$thumbnail = $thumbnail($file);
    self::$thumbnail || Uploader::error('尚未設定縮圖工具！');
    return self::$thumbnail;
  }

  public static function initThumbnail($thumbnail) {
    is_callable($thumbnail) && self::$thumbnail = $thumbnail;
  }

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

  public static function setBaseDirs($baseDirs) {
    is_array($baseDirs) && $baseDirs && self::$baseDirs = $baseDirs;
  }
  
  protected static function saveTool() {
    self::$saveTool !== null || Uploader::error('尚未設定儲存工具！');

    if (is_callable(self::$saveTool) && ($saveTool = self::$saveTool))
      if (!self::$saveTool = $saveTool())
        Uploader::error('尚未設定儲存工具！');

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
    self::$tmpDir !== null && \isReallyWritable(self::$tmpDir) || Uploader::error('Uploader 尚未設定 tmp 目錄或無法寫入！');
    return self::$tmpDir;
  }

  protected static function baseDirs() {
    self::$baseDirs || Uploader::error('Uploader 未指定 baseDirs！');
    return self::$baseDirs;
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
    return ($path = $this->pathDirs($key)) ? self::$baseUrl . implode('/', $path) : $this->d4Url();
  }

  public function pathDirs($fileName = '') {
    return $fileName ? array_merge(self::baseDirs(), $this->getSaveDirs(), [$fileName]) : [];
  }

  public function getSaveDirs() {
    array_key_exists($this->uniqueColumn(), $this->orm->attrs()) || Uploader::error('此物件 「' . get_class($orm) . '」 沒有 「' . $this->uniqueColumn() . '」 欄位！');
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

    if (!$tmp = $this->moveOriFile($fileInfo, $isUseMoveUploadedFile))
      return self::log('[Uploader] put 搬移至暫存資料夾時發生錯誤。');

    if (!$saveDirs = $this->verifySaveDirs())
      return self::log('[Uploader] put 確認儲存路徑發生錯誤。');

    if (!$result = $this->moveFileAndUploadColumn($tmp, $saveDirs, $name))
      return self::log('[Uploader] put 搬移預設位置時發生錯誤。');

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

    if (!file_exists($tmp))
      return self::log('[Uploader] moveOriFile 移動檔案失敗。Path：' . $tmp);

    return $tmp;
  }

  private function verifySaveDirs() {
    $baseDirs = self::baseDirs();
    return array_merge($baseDirs, $this->getSaveDirs());
  }
  
  protected function moveFileAndUploadColumn($tmp, $saveDirs, $oriName) {
    if (!self::saveTool()->put($tmp, $uri = implode ('/', $saveDirs) . '/' . $oriName))
      return self::log('moveFileAndUploadColumn putObject 發生錯誤！', 'Temp：' . $tmp, 'Uri：' . $uri);

    @unlink($tmp) || self::log('moveFileAndUploadColumn 移除舊資料錯誤！');

    if (!$this->uploadColumnAndUpload(''))
      return self::log('moveFileAndUploadColumn uploadColumnAndUpload = "" 錯誤！');

    if (!$this->uploadColumnAndUpload($oriName))
      return self::log('moveFileAndUploadColumn uploadColumnAndUpload = ' . $oriName . ' 錯誤！');

    return true;
  }

  protected function uploadColumnAndUpload($value, $isSave = true) {
    $this->cleanOldFile ();
    return $isSave ? $this->uploadColumn($value) : true;
  }

  protected function cleanOldFile() {
    if ($PathsDirs = $this->getPathsDirs())
      foreach ($PathsDirs as $pathDirs)
        self::saveTool()->delete($pathDir = implode('/', $pathDirs)) || self::log('cleanOldFile 清除檔案發生錯誤！', 'Path：' . $pathDir);
    
    return true;
  }

  public function getPathsDirs() {
    if (!(string)$this->value)
      return [];

    return [array_merge(self::baseDirs(), $this->getSaveDirs(), [(string)$this->value])];
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