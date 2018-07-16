<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

abstract class ImageUploader extends Uploader {
  const ORI = 'ori';
  const SYMBOL = '_';
  const AUTO_FORMAT = true;

  abstract public function versions();

  public function getVersions() {
    $versions = $this->versions();
    return $versions && is_array($versions) ? array_merge([ImageUploader::ORI => []], $versions) : [ImageUploader::ORI => []];
  }

  public function path($key = null) {
    $key !== null || $key = ImageUploader::ORI;
    $versions = $this->getVersions();
    $fileName = array_key_exists($key, $versions) && ($value = (string)$this->value) ? $key . ImageUploader::SYMBOL . $value : '';
    return parent::path($fileName);
  }

  public function paths() {
    $paths = [];

    if (!(string)$this->value)
      return $paths;

    $dir = self::dir();

    foreach ($this->getVersions() as $key => $version)
      array_push($paths, $dir . $this->savePath() . $key . ImageUploader::SYMBOL . $this->value);

    return $paths;
  }

  protected function moveFileAndUploadColumn($tmp, $path, $oriName) {
    $tmpDir = self::tmpDir();
    $versions = $this->getVersions();

    $news = [];
    $info = @exif_read_data($tmp);
    $orientation = $info && isset($info['Orientation']) ? $info['Orientation'] : 0;

    try {
      foreach ($versions as $key => $methods) {
        $image = self::thumbnail($tmp);

        $image->rotate($orientation == 6 ? 90 : ($orientation == 8 ? -90 : ($orientation == 3 ? 180 : 0)));

        $name = !isset($name) ? getRandomName() . (ImageUploader::AUTO_FORMAT ? '.' . $image->getFormat() : '') : $name;
        $newName = $key . ImageUploader::SYMBOL . $name;

        $newPath = $tmpDir . $newName;

        if (!$this->utility($image, $newPath, $key, $methods))
          return self::log('圖像處理失敗！', 'utility 發生錯誤！', '儲存路徑：' . $newPath, '版本' . $key);

        array_push($news, ['name' => $newName, 'path' => $newPath]);
      }
    } catch (\Exception $e) {
      if (!method_exists($e, 'getMessages'))
        return self::log('圖像處理，發生意外錯誤！', '錯誤訊息：' . $e->getMessage());
      else
        return call_user_func_array(['self', 'log'], array_merge(['圖像處理，發生意外錯誤！'], $e->getMessages()));
    }

    if (count($news) != count($versions))
      return self::log('縮圖未完成，有些圖片未完成縮圖！', '成功數量：' . count($news), '版本數量：' . count($versions));

    foreach ($news as $new)
      if (!self::saveTool()->put($new['path'], $path . $new['name']))
        return self::log('Save Tool put 發生錯誤！', '檔案路徑：' . $new['path'], '儲存路徑：' . $path . $new['name']);
      else
        @unlink($new['path']) || self::log('移除暫存資料錯誤！');

    @unlink($tmp) || self::log('移除舊資料錯誤！');

    if (!$this->uploadColumnAndUpload(''))
      return self::log('清空欄位值失敗！');

    if (!$this->uploadColumnAndUpload($name))
      return self::log('設定欄位值失敗！');

    return true;
  }

  private function utility($image, $savePath, $key, $methods) {
    if (!$methods)
      return $image->save($savePath, true);

    foreach ($methods as $method => $params)
      if (!method_exists($image, $method))
        return self::log('縮圖函式沒有此方法！', '縮圖函式：' . $method);
      else
        call_user_func_array([$image, $method], $params);

    return $image->save($savePath, true);
  }

  public function toImageTag($key = null, $attrs = []) { // $attrs = ['class' => 'i']
    return ($url = ($url = $this->url($key)) ? $url : $this->d4Url()) ? '<img src="' . $url . '"' . ($attrs ? ' ' . implode(' ', array_map(function($key, $value) { return $key . '="' . $value . '"'; }, array_keys($attrs), $attrs)) : '') . '>' : '';
  }

  public function toDivImageTag($key = null, $divAttrs = [], $imgAttrs = []) {
    return ($str = $this->toImageTag($key, $imgAttrs)) ? '<div' . ($divAttrs ? ' ' . implode(' ', array_map(function($key, $value) { return $key . '="' . $value . '"'; }, array_keys($divAttrs), $divAttrs)) : '') . '>' . $str . '</div>' : '';
  }
}
