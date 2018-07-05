<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

abstract class ImageUploader extends Uploader {
  const SYMBOL = '_';
  const AUTO_FORMAT = true;

  abstract public function versions();

  private function getVersions() {
    $versions = $this->versions();
    return $versions && is_array($versions) ? array_merge(['' => []], $versions) : ['' => []];
  }

  public function pathDirs($key = '') {
    $versions = $this->getVersions();
    return array_key_exists($key, $versions) && ($value = (string)$this->value) && ($fileName = $key . ImageUploader::SYMBOL . $value) ? parent::pathDirs($fileName) : [];
  }

  public function getPathsDirs() {
    $baseDirs = self::baseDirs();
    $versions = $this->getVersions();
    $paths = [];

    foreach ($versions as $key => $version)
      array_push($paths, array_merge($baseDirs, $this->getSaveDirs(), [$key . ImageUploader::SYMBOL . $this->value]));

    return $paths;
  }

  protected function moveFileAndUploadColumn($temp, $saveDirs, $oriName) {
    $tmpDir = self::tmpDir();
    $versions = $this->getVersions();

    $news = [];
    $info = @exif_read_data($temp);
    $orientation = $info && isset($info['Orientation']) ? $info['Orientation'] : 0;

    try {
      foreach ($versions as $key => $methods) {
        $image = self::thumbnail($temp);

        $image->rotate($orientation == 6 ? 90 : ($orientation == 8 ? -90 : ($orientation == 3 ? 180 : 0)));

        $name = !isset($name) ? getRandomName() . (ImageUploader::AUTO_FORMAT ? '.' . $image->getFormat() : '') : $name;
        $newName = $key . ImageUploader::SYMBOL . $name;

        $newPath = $tmpDir . $newName;

        if (!$this->utility($image, $newPath, $key, $methods))
          return self::log('moveFileAndUploadColumn 圖像處理失敗。');

        array_push($news, ['name' => $newName, 'path' => $newPath]);
      }
    } catch (\Exception $e) {
      return self::log('moveFileAndUploadColumn 圖像處理失敗', 'Message：' . $e->getMessage());
    }

    if (count($news) != count($versions))
      return self::log('moveFileAndUploadColumn 不明原因錯誤。');

    foreach ($news as $new)
      if (!self::saveTool()->put($new['path'], $uri = implode ('/', $saveDirs) . '/' . $new['name']))
        return self::log('moveFileAndUploadColumn putObject 發生錯誤！', 'Temp：' . $new['path'], 'Uri：' . $uri);

    @unlink($new['path']) || self::log('moveFileAndUploadColumn 移除舊資料錯誤！');
    @unlink($temp) || self::log('moveFileAndUploadColumn 移除舊資料錯誤。');

    if (!$this->uploadColumnAndUpload(''))
      return self::log('moveFileAndUploadColumn uploadColumnAndUpload = "" 錯誤。');

    if (!$this->uploadColumnAndUpload($name))
      return self::log('moveFileAndUploadColumn uploadColumnAndUpload = ' . $name . ' 錯誤。');

    return true;
  }

  private function utility($image, $savePath, $key, $methods) {
    if (!$methods)
      return $image->save($savePath, true);

    foreach ($methods as $method => $params)
      if (!is_callable([$image, $method]))
        return self::log('無法呼叫的 Method 錯誤，Method：' . $method);
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
