<?php defined('MAZU') || exit('此檔案不允許讀取！');

Load::sysLib('Thumbnail' . DIRECTORY_SEPARATOR . 'Thumbnail.php');

class ThumbnailGd extends Thumbnail {
  private $options = [
    'resizeUp' => true,
    'interlace' => null,
    'jpegQuality' => 90,
    'preserveAlpha' => true,
    'preserveTransparency' => true,
    'alphaMaskColor' => [255, 255, 255],
    'transparencyMaskColor' => [0, 0, 0]
  ];

  public function __construct($fileName, $options = []) {
    parent::__construct($fileName);

    $this->options = array_merge($this->options, array_intersect_key($options, $this->options));
  }

  protected function allows() {
    return ['gif', 'jpg', 'png'];
  }

  protected function getOldImage($format) {
    switch ($format) {
      case 'gif': return imagecreatefromgif($this->filePath);
      case 'jpg': return imagecreatefromjpeg($this->filePath);
      case 'png': return imagecreatefrompng($this->filePath);
      default: Thumbnail::error('找尋不到符合的格式，或者不支援此檔案格式！', '格式：' . $format);
    }
  }

  public function getDimension($image = null) {
    $image = $image ? $image : $this->getOldImage($this->format);
    return [imagesx($image), imagesy($image)];
  }

  private function _preserveAlpha($image) {
    if ($this->format == 'png' && $this->options['preserveAlpha'] === true) {
      imagealphablending($image, false);
      imagefill($image, 0, 0, imagecolorallocatealpha($image, $this->options['alphaMaskColor'][0], $this->options['alphaMaskColor'][1], $this->options['alphaMaskColor'][2], 0));
      imagesavealpha($image, true);
    }

    if ($this->format == 'gif' && $this->options['preserveTransparency'] === true) {
      imagecolortransparent($image, imagecolorallocate($image, $this->options['transparencyMaskColor'][0], $this->options['transparencyMaskColor'][1], $this->options['transparencyMaskColor'][2]));
      imagetruecolortopalette($image, true, 256);
    }

    return $image;
  }

  private function _copyReSampled($newImage, $oldImage, $newX, $newY, $oldX, $oldY, $newWidth, $newHeight, $oldWidth, $oldHeight) {
    imagecopyresampled($newImage, $oldImage, $newX, $newY, $oldX, $oldY, $newWidth, $newHeight, $oldWidth, $oldHeight);
    return $this->_updateImage($newImage);
  }

  private function _updateImage($image) {
    $this->image = $image;
    $this->dimension = $this->getDimension($this->image);
    return $this;
  }

  public function save($savePath) {
    imageinterlace($this->image, $this->options['interlace'] ? 1 : 0);

    switch ($this->format) {
      case 'jpg': return @imagejpeg($this->image, $savePath, $this->options['jpegQuality']);
      case 'gif': return @imagegif($this->image, $savePath);
      case 'png': return @imagepng($this->image, $savePath);
      default: return false;
    }
  }

  static function verifyColor(&$color) {
    $color = is_string($color) ? Thumbnail::colorHex2Rgb($color) : $color;
    return is_array($color) && (count(array_filter($color, function ($color) { return $color >= 0 && $color <= 255; })) == 3);
  }

  public function pad($width, $height, $color = [255, 255, 255]) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤！', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    if (!ThumbnailGd::verifyColor($color))
      return $this->log('色碼格式錯誤，目前只支援字串 HEX、RGB 陣列格式！', '色碼：' . (is_string($color) ? $color : json_encode($color)));
      
    if ($width < $this->dimension[0] || $height < $this->dimension[1])
      $this->resize($width, $height);

    $newImage = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($width, $height) : imagecreate($width, $height);
    imagefill($newImage, 0, 0, imagecolorallocate($newImage, $color[0], $color[1], $color[2]));

    return $this->_copyReSampled($newImage, $this->image, intval(($width - $this->dimension[0]) / 2), intval(($height - $this->dimension[1]) / 2), 0, 0, $this->dimension[0], $this->dimension[1], $this->dimension[0], $this->dimension[1]);
  }

  private function createNewDimension($width, $height) {
    return [!$this->options['resizeUp'] && ($width > $this->dimension[0]) ? $this->dimension[0] : $width, !$this->options['resizeUp'] && ($height > $this->dimension[1]) ? $this->dimension[1] : $height];
  }

  public function resizeByWidth($width) {
    return $this->resize($width, $width, 'w');
  }

  public function resizeByHeight($height) {
    return $this->resize($height, $height, 'h');
  }

  public function resize($width, $height, $method = 'both') {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤！', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);
      
    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    $newDimension = $this->createNewDimension($width, $height);
    $method = strtolower(trim($method));

    switch ($method) {
      case 'b': case 'both': default:
        $newDimension = $this->calcImageSize($this->dimension, $newDimension);
        break;

      case 'w': case 'width':
        $newDimension = $this->calcWidth($this->dimension, $newDimension);
        break;

      case 'h': case 'height':
        $newDimension = $this->calcHeight($this->dimension, $newDimension);
        break;
    }

    $newImage = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($newDimension[0], $newDimension[1]) : imagecreate($newDimension[0], $newDimension[1]);
    $newImage = $this->_preserveAlpha($newImage);

    return $this->_copyReSampled($newImage, $this->image, 0, 0, 0, 0, $newDimension[0], $newDimension[1], $this->dimension[0], $this->dimension[1]);
  }

  public function adaptiveResizePercent($width, $height, $percent) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤！', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);
    

    if ($percent < 0 || $percent > 100)
      return $this->log('百分比例錯誤！', '百分比要在 0 ~ 100 之間！', '百分比：' . $percent);
    

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    $newDimension = $this->createNewDimension($width, $height);
    $newDimension = $this->calcImageSizeStrict($this->dimension, $newDimension);
    $this->resize($newDimension[0], $newDimension[1]);
    $newDimension = $this->createNewDimension($width, $height);

    $newImage = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($newDimension[0], $newDimension[1]) : imagecreate($newDimension[0], $newDimension[1]);
    $newImage = $this->_preserveAlpha($newImage);

    $cropX = $cropY = 0;

    if ($this->dimension[0] > $newDimension[0])
      $cropX = intval(($percent / 100) * ($this->dimension[0] - $newDimension[0]));
    else if ($this->dimension[1] > $newDimension[1])
      $cropY = intval(($percent / 100) * ($this->dimension[1] - $newDimension[1]));

    return $this->_copyReSampled($newImage, $this->image, 0, 0, $cropX, $cropY, $newDimension[0], $newDimension[1], $newDimension[0], $newDimension[1]);
  }

  public function adaptiveResize($width, $height) {
    return $this->adaptiveResizePercent($width, $height, 50);
  }

  public function resizePercent($percent = 0) {
    if ($percent < 1)
      return $this->log('縮圖比例錯誤！', '百分比要大於 1', '百分比：' . $percent);

    if ($percent == 100)
      return $this;

    $newDimension = $this->calcImageSizePercent($percent, $this->dimension);

    return $this->resize($newDimension[0], $newDimension[1]);
  }

  public function crop($startX, $startY, $width, $height) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤！', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($startX < 0 || $startY < 0)
      return $this->log('起始點錯誤！', '水平、垂直的起始點一定要大於 0', '水平點：' . $startX, '垂直點：' . $startY);

    if ($startX == 0 && $startY == 0 && $width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    $width  = $this->dimension[0] < $width ? $this->dimension[0] : $width;
    $height = $this->dimension[1] < $height ? $this->dimension[1] : $height;
    $startX = ($startX + $width) > $this->dimension[0] ? $this->dimension[0] - $width : $startX;
    $startY = ($startY + $height) > $this->dimension[1] ? $this->dimension[1] - $height : $startY;
    
    $newImage = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($width, $height) : imagecreate($width, $height);
    $newImage = $this->_preserveAlpha($newImage);

    return $this->_copyReSampled($newImage, $this->image, 0, 0, $startX, $startY, $width, $height, $width, $height);
  }

  public function cropFromCenter($width, $height) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤！', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    if ($width > $this->dimension[0] && $height > $this->dimension[1])
      return $this->pad($width, $height);

    $startX = intval(($this->dimension[0] - $width) / 2);
    $startY = intval(($this->dimension[1] - $height) / 2);

    $width  = $this->dimension[0] < $width ? $this->dimension[0] : $width;
    $height = $this->dimension[1] < $height ? $this->dimension[1] : $height;

    return $this->crop($startX, $startY, $width, $height);
  }

  public function rotate($degree, $color = [255, 255, 255]) {
    if (!function_exists('imagerotate'))
      return $this->log('沒有載入 imagerotate 函式！');

    if (!is_numeric($degree))
      return $this->log('角度一定要是數字！', '角度：' . $degree);

    if (!ThumbnailGd::verifyColor($color))
      return $this->log('色碼格式錯誤，目前只支援字串 HEX、RGB 陣列格式！', '色碼：' . (is_string($color) ? $color : json_encode($color)));

    if (!($degree % 360))
      return $this;

    $temp = function_exists('imagecreatetruecolor') ? imagecreatetruecolor(1, 1) : imagecreate(1, 1);
    $newImage = imagerotate($this->image, 0 - $degree, imagecolorallocate($temp, $color[0], $color[1], $color[2]));

    return $this->_updateImage($newImage);
  }

  public function adaptiveResizeQuadrant($width, $height, $item = 'c') {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤！', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    $newDimension = $this->createNewDimension($width, $height);
    $newDimension = $this->calcImageSizeStrict($this->dimension, $newDimension);
    $this->resize($newDimension[0], $newDimension[1]);

    $newDimension = $this->createNewDimension($width, $height);
    $newImage = function_exists('imagecreatetruecolor') ? imagecreatetruecolor($newDimension[0], $newDimension[1]) : imagecreate($newDimension[0], $newDimension[1]);
    $newImage = $this->_preserveAlpha($newImage);

    $cropX = $cropY = 0;
    $item = strtolower(trim($item));

    if ($this->dimension[0] > $newDimension[0]) {
      switch ($item) {
        case 'l': case 'left':
          $cropX = 0;
          break;

        case 'r': case 'right':
          $cropX = intval($this->dimension[0] - $newDimension[0]);
          break;

        case 'c': case 'center': default:
          $cropX = intval(($this->dimension[0] - $newDimension[0]) / 2);
          break;
      }
    } else if ($this->dimension[1] > $newDimension[1]) {
      switch ($item) {
        case 't': case 'top': 
          $cropY = 0;
          break;

        case 'b': case 'bottom':
          $cropY = intval($this->dimension[1] - $newDimension[1]);
          break;

        case 'c': case 'center': default:
          $cropY = intval(($this->dimension[1] - $newDimension[1]) / 2);
          break;
      }
    }

    return $this->_copyReSampled($newImage, $this->image, 0, 0, $cropX, $cropY, $newDimension[0], $newDimension[1], $newDimension[0], $newDimension[1]);
  }

  public static function create($filePath, $options = []) {
    return new static($filePath, $options);
  }

  public static function block9($files, $savePath, $interlace = null, $jpegQuality = 100) {
    count($files) >= 9 || Thumbnail::error('參數錯誤！', '檔案數量要大於等於 9', '數量：' . count($files));
    $savePath          || Thumbnail::error('錯誤的儲存路徑！', '儲存路徑：' . $savePath);

    $positions = [
      ['left' =>   2, 'top' =>   2, 'width' => 130, 'height' => 130], ['left' => 134, 'top' =>   2, 'width' =>  64, 'height' =>  64], ['left' => 200, 'top' =>   2, 'width' =>  64, 'height' =>  64],
      ['left' => 134, 'top' =>  68, 'width' =>  64, 'height' =>  64], ['left' => 200, 'top' =>  68, 'width' =>  64, 'height' =>  64], ['left' =>   2, 'top' => 134, 'width' =>  64, 'height' =>  64],
      ['left' =>  68, 'top' => 134, 'width' =>  64, 'height' =>  64], ['left' => 134, 'top' => 134, 'width' =>  64, 'height' =>  64], ['left' => 200, 'top' => 134, 'width' =>  64, 'height' =>  64],
    ];

    $image = imagecreatetruecolor(266, 200);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

    for ($i = 0, $c = count($positions); $i < $c; $i++)
      imagecopymerge($image, Thumbnail::createGd($files[$i])->adaptiveResizeQuadrant($positions[$i]['width'], $positions[$i]['height'])->getImage(), $positions[$i]['left'], $positions[$i]['top'], 0, 0, $positions[$i]['width'], $positions[$i]['height'], 100);

    isset($interlace) && imageinterlace($image, $interlace ? 1 : 0);

    switch (pathinfo($savePath, PATHINFO_EXTENSION)) {
      case 'jpg':          return @imagejpeg($image, $savePath, $jpegQuality);
      case 'gif':          return @imagegif($image, $savePath);
      case 'png': default: return @imagepng($image, $savePath);
    }
  }

  public static function photos($files, $savePath, $interlace = null, $jpegQuality = 100) {
    $files    || Thumbnail::error('參數錯誤！', '檔案數量要大於等於 1', '數量：' . count($files));
    $savePath || Thumbnail::error('錯誤的儲存路徑！', '儲存路徑：' . $savePath);

    $w = 1200;
    $h = 630;

    $image = imagecreatetruecolor($w, $h);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

    $spacing = 5;
    $positions = [];

    switch (count($files)) {
      case 1:          $positions = [['left' => 0, 'top' => 0, 'width' => $w, 'height' => $h]]; break;
      case 2:          $positions = [['left' => 0, 'top' => 0, 'width' => $w / 2 - $spacing, 'height' => $h], ['left' => $w / 2 + $spacing, 'top' => 0, 'width' => $w / 2 - $spacing, 'height' => $h]]; break;
      case 3:          $positions = [['left' => 0, 'top' => 0, 'width' => $w / 2 - $spacing, 'height' => $h], ['left' => $w / 2 + $spacing, 'top' => 0, 'width' => $w / 2 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 2 + $spacing, 'top' => $h / 2 + $spacing, 'width' => $w / 2 - $spacing, 'height' => $h / 2 - $spacing]]; break;
      case 4:          $positions = [['left' => 0, 'top' => 0, 'width' => $w, 'height' => $h / 2 - $spacing], ['left' => 0, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 3 + $spacing, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing]]; break;
      case 5:          $positions = [['left' => 0, 'top' => 0, 'width' => $w / 2 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 2 + $spacing, 'top' => 0, 'width' => $w / 2 - $spacing, 'height' => $h / 2 - $spacing], ['left' => 0, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 3 + $spacing, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing]]; break;
      case 6:          $positions = [['left' => 0, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 3 + $spacing, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => 0, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 3 + $spacing, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing]]; break;
      case 7:          $positions = [['left' => 0, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => 0, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 3 + $spacing, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => $w / 3 + $spacing, 'top' => $h / 3 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => $w / 3 + $spacing, 'top' => ($h / 3 + $spacing) * 2, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing]]; break;
      case 8:          $positions = [['left' => 0, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => 0, 'top' => $h / 3 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => 0, 'top' => ($h / 3 + $spacing) * 2, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => $w / 3 + $spacing, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => $w / 3 + $spacing, 'top' => $h / 2 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 2 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => $h / 3 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => ($h / 3 + $spacing) * 2, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing]]; break;
      default: case 9: $positions = [['left' => 0, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => 0, 'top' => $h / 3 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => 0, 'top' => ($h / 3 + $spacing) * 2, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => $w / 3 + $spacing, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => $w / 3 + $spacing, 'top' => $h / 3 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => $w / 3 + $spacing, 'top' => ($h / 3 + $spacing) * 2, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => 0, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => $h / 3 + $spacing, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing], ['left' => ($w / 3 + $spacing) * 2, 'top' => ($h / 3 + $spacing) * 2, 'width' => $w / 3 - $spacing, 'height' => $h / 3 - $spacing]]; break;
    }

    for ($i = 0, $c = count($positions); $i < $c; $i++)
      imagecopymerge($image, Thumbnail::createGd($files[$i])->adaptiveResizeQuadrant($positions[$i]['width'], $positions[$i]['height'])->getImage(), $positions[$i]['left'], $positions[$i]['top'], 0, 0, $positions[$i]['width'], $positions[$i]['height'], 100);

    isset($interlace) && imageinterlace($image, $interlace ? 1 : 0);

    switch (pathinfo($savePath, PATHINFO_EXTENSION)) {
      case 'jpg':          return @imagejpeg($image, $savePath, $jpegQuality);
      case 'gif':          return @imagegif($image, $savePath);
      default: case 'png': return @imagepng($image, $savePath);
    }
  }
}
