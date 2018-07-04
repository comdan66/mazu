<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

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
    if (self::tmpDirNotWritable())
      return self::log('Tmp 資料夾無法寫入！');
    
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
