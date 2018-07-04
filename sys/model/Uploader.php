<?php

namespace M;

abstract class Uploader {
  protected static $driver = null;
  protected static $baseDirs = ['upload'];
  protected static $tmpDir = null;
  protected static $baseUrl = '/';
  protected static $s3Bucket = null;
  
  public static function setDriver($driver) {
    is_string($driver) && self::$driver = $driver;
  }

  public static function setTmpDir($tmpDir) {
    is_writable($tmpDir) && self::$tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }

  public static function setBaseDirs($baseDirs) {
    is_array($baseDirs) && self::$baseDirs = $baseDirs;
  }

  public static function setBaseUrl($baseUrl) {
    is_string($baseUrl) && self::$baseUrl = rtrim($baseUrl, '/') . '/';
  }

  public static function setS3Bucket($s3Bucket) {
    is_string($s3Bucket) && self::$s3Bucket = trim($s3Bucket, '/');
  }

  public static function bind($orm, $column) {
    $class = get_called_class();
    return new $class($orm, $column);
  }

  protected $orm = null;
  protected $column = null;
  protected $value = null;

  public function __construct($orm, $column) {
    $attrs = $orm->attrs();
    array_key_exists($this->uniqueColumn(), $attrs)           || \_M\Config::error('[Uploader] Class 「' . get_class($orm) . '」 無 「' . $this->uniqueColumn() . '」 欄位。');
    self::$tmpDir !== null                                    || \_M\Config::error('[Uploader] 尚未設定 Tmp 目錄。');
    is_writable(self::$tmpDir)                                || \_M\Config::error('[Uploader] Tmp 目錄沒有權限寫入。tmpDir：' . self::$tmpDir);
    self::$driver !== null                                    || \_M\Config::error('[Uploader] 尚未設定 Driver 目錄。');
    in_array(self::$driver, ['s3', 'local'])                  || \_M\Config::error('[Uploader] Driver 只允許 s3、local。driver：' . self::$driver);
    self::$driver == 'local' || class_exists('S3')            || \_M\Config::error('[Uploader] 尚未初始 S3 物件。');
    self::$driver == 'local' || self::$s3Bucket !== null      || \_M\Config::error('[Uploader] 未給予 S3 Bucket。');

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
    return is_numeric($id = $this->orm->attrs($this->uniqueColumn(), 0)) ? array_merge([$this->orm->getTableName(), $this->column], str_split(sprintf('%08s', dechex($id)), 2)) : [$this->orm->getTableName(), $this->column];
  }

  protected function d4Url() {
    return '';
  }


  protected static function log($log) {
    \_M\Config::log($log);
    return false;
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
    $format = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $temp = downloadWebFile($url, self::$tmpDir . getRandomName() . ($format ? '.' . $format : ''));
    return $temp && $this->put($temp, false) ? file_exists($temp) ? @unlink($temp) : true : false;
  }
}

abstract class FileUploader extends Uploader {
  public function url($url ='') {
    return parent::url('');
  }

  public function pathDirs($fileName = '') {
    return parent::pathDirs((string)$this->value);
  }

  public function link($text, $attrs = []) { // $attrs = array ('class' => 'i')
    return ($url = ($url = $this->url()) ? $url : '') ? '<a href="' . $url . '"' . ($attrs ? ' ' . implode(' ', array_map(function($key, $value) { return $key . '="' . $value . '"'; }, array_keys($attrs), $attrs)) : '') . '>' . $text . '</a>' : '';
  }
}

abstract class ImageUploader extends Uploader {
  const SYMBOL = '_';
  const AUTO_FORMAT = true;

  abstract public function versions();

  private function getVersions() {
    return ($versions = $this->versions()) && is_array($versions) ? array_merge(['' => []], $versions) : ['' => []];
  }

  public function pathDirs($key = '') {
    $versions = $this->getVersions();
    return array_key_exists($key, $versions) && ($value = (string)$this->value) && ($fileName = $key . ImageUploader::SYMBOL . $value) ? parent::pathDirs($fileName) : [];
  }

  public function getPathsDirs() {
    $versions = $this->getVersions();

    $paths = [];
    foreach ($versions as $key => $version)
      array_push($paths, array_merge(self::$baseDirs, $this->getSaveDirs(), [$key . ImageUploader::SYMBOL . $this->value]));

    return $paths;
  }

  protected function moveFileAndUploadColumn($temp, $saveDirs, $oriName) {
    $versions = $this->getVersions();

    if (!class_exists('Thumbnail'))
      return self::log('[ImageUploader] 找不到 Thumbnail 縮圖物件');

    $news = [];
    $info = @exif_read_data($temp);
    $orientation = $info && isset($info['Orientation']) ? $info['Orientation'] : 0;

    try {
      foreach ($versions as $key => $methods) {
        $image = \Thumbnail::create($temp);

        $image->rotate($orientation == 6 ? 90 : ($orientation == 8 ? -90 : ($orientation == 3 ? 180 : 0)));

        $name = !isset($name) ? getRandomName() . (ImageUploader::AUTO_FORMAT ? '.' . $image->getFormat() : '') : $name;
        $newName = $key . ImageUploader::SYMBOL . $name;

        $newPath = self::$tmpDir . $newName;

        if (!$this->utility($image, $newPath, $key, $methods))
          return self::log('[ImageUploader] moveFileAndUploadColumn 圖像處理失敗。');

        array_push($news, ['name' => $newName, 'path' => $newPath]);
      }
    } catch (\Exception $e) {
      return self::log('[ImageUploader] moveFileAndUploadColumn 圖像處理失敗', 'Message：' . $e->getMessage());
    }

    if (count($news) != count($versions))
      return self::log('[ImageUploader] moveFileAndUploadColumn 不明原因錯誤。');

    switch (self::$driver) {
      case 'local':
        foreach ($news as $new)
          if (!@rename($new['path'], $path = implode(DIRECTORY_SEPARATOR, $saveDirs) . DIRECTORY_SEPARATOR . $new['name']))
            return self::log('[ImageUploader] moveFileAndUploadColumn local rename 搬移預設位置時發生錯誤。Path：' . $path);
        break;

      case 's3':
        foreach ($news as $new) {
          if (!\S3::putObject($new['path'], self::$s3Bucket, $uri = implode ('/', $saveDirs) . '/' . $new['name']))
            return self::log('[ImageUploader] moveFileAndUploadColumn s3 putObject 丟至 S3 發生錯誤。Bucket：' . self::$s3Bucket . '，uri：' . $uri);
          @unlink($new['path']) || self::log('[ImageUploader] moveFileAndUploadColumn s3 移除舊資料錯誤。');
        }
        break;
    }

    @unlink($temp) || self::log('[ImageUploader] moveFileAndUploadColumn 移除舊資料錯誤。');

    if (!$this->uploadColumnAndUpload(''))
      return self::log('[ImageUploader] moveFileAndUploadColumn uploadColumnAndUpload = "" 錯誤。');

    if (!$this->uploadColumnAndUpload($name))
      return self::log('[ImageUploader] moveFileAndUploadColumn uploadColumnAndUpload = ' . $name . ' 錯誤。');

    return true;
  }

  private function utility($image, $savePath, $key, $methods) {
    if (!$methods)
      return $image->save($savePath, true);

    foreach ($methods as $method => $params)
      if (!is_callable([$image, $method]))
        return self::log('[ImageUploader] 無法呼叫的 Method 錯誤，Method：' . $method);
      else
        call_user_func_array([$image, $method], $params);

    return $image->save($savePath, true);
  }

  public function toImageTag($key = '', $attrs = []) { // $attrs = ['class' => 'i']
    return ($url = ($url = $this->url($key)) ? $url : $this->d4Url()) ? '<img src="' . $url . '"' . ($attrs ? ' ' . implode(' ', array_map(function($key, $value) { return $key . '="' . $value . '"'; }, array_keys($attrs), $attrs)) : '') . '>' : '';
  }

  public function toDivImageTag($key = '', $divAttrs = [], $imgAttrs = []) {
    return ($str = $this->toImageTag($key, $imgAttrs)) ? '<div' . ($divAttrs ? ' ' . implode(' ', array_map(function($key, $value) { return $key . '="' . $value . '"'; }, array_keys($divAttrs), $divAttrs)) : '') . '>' . $str . '</div>' : '';
  }
}
