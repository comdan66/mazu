<?php defined('MAZU') || exit('此檔案不允許讀取！');

class ThumbnailException extends Exception {
  private $messages = [];

  public function __construct($messages) {
    $this->messages = $messages;
  }

  public function getMessages() {
    return $this->messages;
  }
}

abstract class Thumbnail {
  private $class = null;
  protected $filePath = null;

  protected $mime = null;
  protected $format = null;
  protected $image = null;
  protected $dimension = null;

  public function __construct($filePath) {
    is_file($filePath) && is_readable($filePath) || Thumbnail::error('檔案不可讀取，或者不存在！', '檔案路徑：' . $filePath);
    
    $this->class = get_called_class();
    $this->filePath = $filePath;
   
    $this->init();
  }

  abstract protected function allows();

  protected function log() {
    call_user_func_array('Log::thumbnail', func_get_args());
    return $this;
  }

  protected function init() {
    function_exists('mime_content_type') || Thumbnail::error('mime_content_type 函式不存在！');

    if (!$this->mime = strtolower(mime_content_type($this->filePath)))
      Thumbnail::error('取不到檔案的 mime 格式！', '檔案路徑：' . $this->filePath);

    if (($this->format = self::getExtensionByMime($this->mime)) === false)
      Thumbnail::error('取不到符合的格式！', 'Mime：' . $this->mime);

    if (static::allows())
      if (!in_array($this->format, static::allows()))
        Thumbnail::error('不支援此檔案格式！', '格式：' . $this->format, '只允許：' . json_encode(static::allows()));

    $this->image = $this->class == 'ThumbnailImagick' ? new Imagick($this->filePath) : $this->getOldImage($this->format);

    $this->image || Thumbnail::error('產生 image 物件失敗！');

    $this->dimension = $this->getDimension($this->image);
  }

  public function getFormat() {
    return $this->format;
  }

  public function getImage() {
    return $this->image;
  }

  protected function calcImageSizePercent($percent, $dimension) {
    return [ceil($dimension[0] * $percent / 100), ceil($dimension[1] * $percent / 100)];
  }

  protected function calcWidth($oldDimension, $newDimension) {
    $newWidthPercentage = 100 * $newDimension[0] / $oldDimension[0];
    $height = ceil($oldDimension[1] * $newWidthPercentage / 100);
    return [$newDimension[0], $height];
  }

  protected function calcHeight($oldDimension, $newDimension) {
    $newHeightPercentage  = 100 * $newDimension[1] / $oldDimension[1];
    $width = ceil($oldDimension[0] * $newHeightPercentage / 100);
    return [$width, $newDimension[1]];
  }

  protected function calcImageSize($oldDimension, $newDimension) {
    $newSize = [$oldDimension[0], $oldDimension[1]];

    if ($newDimension[0] > 0) {
      $newSize = $this->calcWidth ($oldDimension, $newDimension);
      ($newDimension[1] > 0) && ($newSize[1] > $newDimension[1]) && $newSize = $this->calcHeight($oldDimension, $newDimension);
    }
    if ($newDimension[1] > 0) {
      $newSize = $this->calcHeight($oldDimension, $newDimension);
      ($newDimension[0] > 0) && ($newSize[0] > $newDimension[0]) && $newSize = $this->calcWidth($oldDimension, $newDimension);
    }
    return $newSize;
  }

  protected function calcImageSizeStrict($oldDimension, $newDimension) {
    $newSize = [$newDimension[0], $newDimension[1]];

    if ($newDimension[0] >= $newDimension[1]) {
      if ($oldDimension[0] > $oldDimension[1])  {
        $newSize = $this->calcHeight($oldDimension, $newDimension);
        $newSize[0] < $newDimension[0] && $newSize = $this->calcWidth($oldDimension, $newDimension);
      } else if ($oldDimension[1] >= $oldDimension[0]) {
        $newSize = $this->calcWidth($oldDimension, $newDimension);
        $newSize[1] < $newDimension[1] && $newSize = $this->calcHeight($oldDimension, $newDimension);
      }
    } else if ($newDimension[1] > $newDimension[0]) {
      if ($oldDimension[0] >= $oldDimension[1]) {
        $newSize = $this->calcWidth($oldDimension, $newDimension);
        $newSize[1] < $newDimension[1] && $newSize = $this->calcHeight($oldDimension, $newDimension);
      } else if ($oldDimension[1] > $oldDimension[0]) {
        $newSize = $this->calcHeight($oldDimension, $newDimension);
        $newSize[0] < $newDimension[0] && $newSize = $this->calcWidth($oldDimension, $newDimension);
      }
    }
    return $newSize;
  }

  private static function getExtensionByMime($m) {
    static $extensions;

    if (isset($extensions[$m]))
      return $extensions[$m];

    foreach (config('extension') as $ext => $mime)
      if (in_array($m, $mime))
        return $extensions[$m] = $ext;

    return $extensions[$m] = false;
  }
  
  protected static function error() {
    $backtrace = debug_backtrace();
    throw new ThumbnailException(func_get_args());
  }

  public static function colorHex2Rgb($hex) {
    if (($hex = str_replace('#', '', $hex)) && ((strlen($hex) == 3) || (strlen($hex) == 6))) {
      if(strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
      } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
      }
      return [$r, $g, $b];
    } else {
      return [];
    }
  }

  public static function sort2DArr($key, $list) {
    if (!$list)
      return $list;

    $tmp = [];
    foreach ($list as &$ma)
      $tmp[] = &$ma[$key];
    array_multisort($tmp, SORT_DESC, $list);

    return $list;
  }
}
