<?php defined('MAZU') || exit('此檔案不允許讀取！');

Load::sysLib('Thumbnail' . DIRECTORY_SEPARATOR . 'Thumbnail.php');

class ThumbnailImagick extends Thumbnail {
  protected static $allows = ['gif', 'jpg', 'png'];

  private $options = [
    'resizeUp' => true,
  ];

  public function __construct($fileName, $options = []) {
    parent::__construct($fileName);

    $this->options = array_merge($this->options, array_intersect_key($options, $this->options));
  }

  public function getDimension($image = null) {
    $image || $image = clone $this->image;
    (($imagePage = $image->getImagePage()) && isset($imagePage['width'], $imagePage['height']) && $imagePage['width'] > 0 && $imagePage['height'] > 0) || (($imagePage = $image->getImageGeometry()) && isset($imagePage['width'], $imagePage['height']) && $imagePage['width'] > 0 && $imagePage['height'] > 0) || Thumbnail::error('無法取得尺寸');
    return [$imagePage['width'], $imagePage['height']];
  }

  private function _machiningImageResize($newDimension) {
    $newImage = clone $this->image;
    $newImage = $newImage->coalesceImages();

    if ($this->format == 'gif')
      do {
        $newImage->thumbnailImage($newDimension[0], $newDimension[1], false);
      } while ($newImage->nextImage() || !$newImage = $newImage->deconstructImages());
    else
      $newImage->thumbnailImage($newDimension[0], $newDimension[1], false);

    return $newImage;
  }

  private function _machiningImageCrop($cropX, $cropY, $width, $height, $color = 'transparent') {
    $newImage = new Imagick();
    $newImage->setFormat($this->format);

    if ($this->format == 'gif') {
      $imagick = clone $this->image;
      $imagick = $imagick->coalesceImages();
      
      do {
        $temp = new Imagick();
        $temp->newImage($width, $height, new ImagickPixel($color));
        $imagick->chopImage($cropX, $cropY, 0, 0);
        $temp->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, 0, 0);

        $newImage->addImage($temp);
        $newImage->setImageDelay($imagick->getImageDelay ());
      } while ($imagick->nextImage());
    } else {
      $imagick = clone $this->image;
      $imagick->chopImage($cropX, $cropY, 0, 0);
      $newImage->newImage($width, $height, new ImagickPixel($color));
      $newImage->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, 0, 0);
    }
    return $newImage;
  }

  private function _machiningImageRotate($degree, $color = 'transparent') {
    $newImage = new Imagick();
    $newImage->setFormat($this->format);
    $imagick = clone $this->image;

    if ($this->format == 'gif') {
      $imagick->coalesceImages();
      
      do {
        $temp = new Imagick();
        $imagick->rotateImage(new ImagickPixel($color), $degree);
        $newDimension = $this->getDimension($imagick);
        $temp->newImage($newDimension[0], $newDimension[1], new ImagickPixel($color));
        $temp->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, 0, 0);
        $newImage->addImage($temp);
        $newImage->setImageDelay($imagick->getImageDelay());
      } while ($imagick->nextImage());
    } else {
      $imagick->rotateImage(new ImagickPixel($color), $degree);
      $newDimension = $this->getDimension($imagick);
      $newImage->newImage($newDimension[0], $newDimension[1], new ImagickPixel($color));
      $newImage->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, 0, 0);
    }
    return $newImage;
  }

  private function _updateImage($image) {
    $this->image = $image;
    $this->dimension = $this->getDimension($image);
    return $this;
  }

  private function _machiningImageFilter($radius, $sigma, $channel) {
    if ($this->format == 'gif') {
      $newImage = clone $this->image;
      $newImage = $newImage->coalesceImages();

      do {
        $newImage->adaptiveBlurImage($radius, $sigma, $channel);
      } while ($newImage->nextImage() || !$newImage = $newImage->deconstructImages());
    } else {
      $newImage = clone $this->image;
      $newImage->adaptiveBlurImage($radius, $sigma, $channel);
    }
    return $newImage;
  }

  private function _createFont($font, $fontSize, $color, $alpha) {
    $draw = new ImagickDraw();
    $draw->setFont($font);
    $draw->setFontSize($fontSize);
    $draw->setFillColor($color);
    // $draw->setFillAlpha ($alpha);
    return $draw;
  }

  public function save($savePath, $rawData = true) {
    return $savePath ? $this->image->writeImages($savePath, $rawData) : Thumbnail::error ('錯誤的儲存路徑', '路徑：' . $savePath);
  }

  public function pad($width, $height, $color = 'transparent') {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    if (!is_string($color))
      return $this->log('色碼格式錯誤，目前只支援字串 HEX 格式', '色碼：' . json_encode($color));

    if ($width < $this->dimension[0] || $height < $this->dimension[1])
      $this->resize($width, $height);

    $newImage = new Imagick();
    $newImage->setFormat($this->format);

    if ($this->format == 'gif') {
      $imagick = clone $this->image;
      $imagick = $imagick->coalesceImages();

      do {
        $temp = new Imagick();
        $temp->newImage($width, $height, new ImagickPixel($color));
        $temp->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, intval(($width - $this->dimension[0]) / 2), intval(($height - $this->dimension[1]) / 2));
        $newImage->addImage($temp);
        $newImage->setImageDelay($imagick->getImageDelay());
      } while ($imagick->nextImage());
    } else {
      $newImage->newImage($width, $height, new ImagickPixel($color));
      $newImage->compositeImage(clone $this->image, Imagick::COMPOSITE_DEFAULT, intval(($width - $this->dimension[0]) / 2), intval(($height - $this->dimension[1]) / 2));
    }

    return $this->_updateImage($newImage);
  }

  private function createNewDimension ($width, $height) {
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
      return $this->log('新尺寸錯誤', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

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

    $workingImage = $this->_machiningImageResize($newDimension);

    return $this->_updateImage($workingImage);
  }

  public function adaptiveResizePercent($width, $height, $percent) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($percent < 0 || $percent > 100)
      return $this->log('百分比例錯誤', '百分比要在 0 ~ 100 之間', 'Percent：' . $percent);

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;
    
    $newDimension = $this->createNewDimension($width, $height);
    $newDimension = $this->calcImageSizeStrict($this->dimension, $newDimension);
    $this->resize($newDimension[0], $newDimension[1]);
    $newDimension = $this->createNewDimension($width, $height);

    $cropX = $cropY = 0;

    if ($this->dimension[0] > $newDimension[0])
      $cropX = intval(($percent / 100) * ($this->dimension[0] - $newDimension[0]));
    else if ($this->dimension[1] > $newDimension[1])
      $cropY = intval(($percent / 100) * ($this->dimension[1] - $newDimension[1]));

    $workingImage = $this->_machiningImageCrop($cropX, $cropY, $newDimension[0], $newDimension[1]);
    return $this->_updateImage($workingImage);
  }

  public function adaptiveResize($width, $height) {
    return $this->adaptiveResizePercent($width, $height, 50);
  }

  public function resizePercent($percent = 0) {
    if ($percent < 1)
      return $this->log('縮圖比例錯誤', '百分比要大於 1', 'Percent：' . $percent);

    if ($percent == 100)
      return $this;

    $newDimension = $this->calcImageSizePercent($percent, $this->dimension);
    return $this->resize($newDimension[0], $newDimension[1]);
  }

  public function crop($startX, $startY, $width, $height) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($startX < 0 || $startY < 0)
      return $this->log('起始點錯誤', '水平、垂直的起始點一定要大於 0', '水平點：' . $startX, '垂直點：' . $startY);

    if ($startX == 0 && $startY == 0 && $width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    $width  = $this->dimension[0] < $width ? $this->dimension[0] : $width;
    $height = $this->dimension[1] < $height ? $this->dimension[1] : $height;

    $startX + $width > $this->dimension[0] && $startX = $this->dimension[0] - $width;
    $startY + $height > $this->dimension[1] && $startY = $this->dimension[1] - $height;

    $workingImage = $this->_machiningImageCrop($startX, $startY, $width, $height);
    return $this->_updateImage($workingImage);
  }

  public function cropFromCenter($width, $height) {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

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

  public function rotate($degree, $color = 'transparent') {
    if (!is_numeric($degree))
      return $this->log('角度一定要是數字', 'Degree：' . $degree);

    if (!is_string($color))
      return $this->log('色碼格式錯誤，目前只支援字串 HEX 格式', '色碼：' . json_encode($color));

    if (!($degree % 360))
      return $this;

    $workingImage = $this->_machiningImageRotate($degree, $color);

    return $this->_updateImage($workingImage);
  }

  public function adaptiveResizeQuadrant($width, $height, $item = 'c') {
    $width = intval($width);
    $height = intval($height);

    if ($width <= 0 || $height <= 0)
      return $this->log('新尺寸錯誤', '尺寸寬高一定要大於 0', '寬：' . $width, '高：' . $height);

    if ($width == $this->dimension[0] && $height == $this->dimension[1])
      return $this;

    $newDimension = $this->createNewDimension($width, $height);
    $newDimension = $this->calcImageSizeStrict($this->dimension, $newDimension);
    $this->resize($newDimension[0], $newDimension[1]);
    $newDimension = $this->createNewDimension($width, $height);
    
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

    $workingImage = $this->_machiningImageCrop($cropX, $cropY, $newDimension[0], $newDimension[1]);

    return $this->_updateImage($workingImage);
  }

  public static function block9($files, $savePath = null, $rawData = true) {
    count($files) >= 9 || Thumbnail::error('參數錯誤', '檔案數量要大於等於 9', '數量：' . count($files));
    $savePath          || Thumbnail::error('錯誤的儲存路徑', '路徑：' . $savePath);

    $newImage = new Imagick();
    $newImage->newImage(266, 200, new ImagickPixel('white'));
    $newImage->setFormat(pathinfo($savePath, PATHINFO_EXTENSION));

    $positions = [
      ['left' =>   2, 'top' =>   2, 'width' => 130, 'height' => 130], ['left' => 134, 'top' =>   2, 'width' =>  64, 'height' =>  64], ['left' => 200, 'top' =>   2, 'width' =>  64, 'height' =>  64],
      ['left' => 134, 'top' =>  68, 'width' =>  64, 'height' =>  64], ['left' => 200, 'top' =>  68, 'width' =>  64, 'height' =>  64], ['left' =>   2, 'top' => 134, 'width' =>  64, 'height' =>  64],
      ['left' =>  68, 'top' => 134, 'width' =>  64, 'height' =>  64], ['left' => 134, 'top' => 134, 'width' =>  64, 'height' =>  64], ['left' => 200, 'top' => 134, 'width' =>  64, 'height' =>  64],
    ];

    for ($i = 0, $c = count($positions); $i < $c; $i++)
      $newImage->compositeImage(Thumbnail::createImagick($files[$i])->adaptiveResizeQuadrant($positions[$i]['width'], $positions[$i]['height'])->getImage(), Imagick::COMPOSITE_DEFAULT, $positions[$i]['left'], $positions[$i]['top']);

    return $newImage->writeImages($savePath, $rawData);
  }

  public static function photos($files, $savePath = null, $rawData = true) {
    $files    || Thumbnail::error ('參數錯誤', '檔案數量要大於等於 1', '數量：' . count($files));
    $savePath || Thumbnail::error('錯誤的儲存路徑', '路徑：' . $savePath);
    
    $w = 1200;
    $h = 630;

    $newImage = new Imagick();
    $newImage->newImage($w, $h, new ImagickPixel('white'));
    $newImage->setFormat(pathinfo ($savePath, PATHINFO_EXTENSION));
    
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
      $newImage->compositeImage(Thumbnail::createImagick($files[$i])->adaptiveResizeQuadrant($positions[$i]['width'], $positions[$i]['height'])->getImage(), Imagick::COMPOSITE_DEFAULT, $positions[$i]['left'], $positions[$i]['top']);

    return $newImage->writeImages($savePath, $rawData);
  }

  public static function create($filePath, $options = []) {
    return new static($filePath, $options);
  }

  public function filter($radius, $sigma, $channel = Imagick::CHANNEL_DEFAULT) {
    $items = [Imagick::CHANNEL_UNDEFINED, Imagick::CHANNEL_RED,     Imagick::CHANNEL_GRAY,  Imagick::CHANNEL_CYAN,
              Imagick::CHANNEL_GREEN,     Imagick::CHANNEL_MAGENTA, Imagick::CHANNEL_BLUE,  Imagick::CHANNEL_YELLOW,
              Imagick::CHANNEL_ALPHA,     Imagick::CHANNEL_OPACITY, Imagick::CHANNEL_BLACK,
              Imagick::CHANNEL_INDEX,     Imagick::CHANNEL_ALL,     Imagick::CHANNEL_DEFAULT];

    if (!is_numeric($radius))
      return $this->log('參數錯誤', '參數 Radius 要為數字', 'Radius：' . $radius);

    if (!is_numeric($sigma))
      return $this->log('參數錯誤', '參數 Sigma 要為數字', 'Sigma：' . $sigma);

    if (!in_array($channel, $items))
      return $this->log('參數錯誤', '參數 Channel 格式不正確', 'Channel：' . $channel);

    $workingImage = $this->_machiningImageFilter($radius, $sigma, $channel);

    return $this->_updateImage($workingImage);
  }

  public function lomography() {
    $newImage = new Imagick();
    $newImage->setFormat($this->format);

    if ($this->format == 'gif') {
      $imagick = clone $this->image;
      $imagick = $imagick->coalesceImages();
      
      do {
        $temp = new Imagick();
        $imagick->setimagebackgroundcolor("black");
        $imagick->gammaImage(0.75);
        $imagick->vignetteImage(0, max($this->dimension[0], $this->dimension[1]) * 0.2, 0 - ($this->dimension[0] * 0.05), 0 - ($this->dimension[1] * 0.05));
        $temp->newImage($this->dimension[0], $this->dimension[1], new ImagickPixel('transparent'));
        $temp->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, 0, 0);

        $newImage->addImage($temp);
        $newImage->setImageDelay($imagick->getImageDelay());
      } while ($imagick->nextImage());
    } else {
      $newImage = clone $this->image;
      $newImage->setimagebackgroundcolor("black");
      $newImage->gammaImage(0.75);
      $newImage->vignetteImage(0, max($this->dimension[0], $this->dimension[1]) * 0.2, 0 - ($this->dimension[0] * 0.05), 0 - ($this->dimension[1] * 0.05));
    }
    return $this->_updateImage($newImage);
  }

  public function getAnalysisDatas($maxCount = 10) {
    if ($maxCount <= 0) {
      $this->log('參數錯誤', '參數 Max Count 一定要大於 0', 'Max Count：' . $maxCount);
      return [];
    }

    $temp = clone $this->image;

    $temp->quantizeImage($maxCount, Imagick::COLORSPACE_RGB, 0, false, false );
    $pixels = $temp->getImageHistogram();

    $datas = [];
    $index = 0;
    $pixelCount = $this->dimension[0] * $this->dimension[1];

    if ($pixels && $maxCount)
      foreach ($pixels as $pixel)
        if ($index++ < $maxCount)
          array_push($datas, array ('color' => $pixel->getColor(), 'count' => $pixel->getColorCount(), 'percent' => round($pixel->getColorCount() / $pixelCount * 100)));
        else
          break;

    return Thumbnail::sort2DArr('count', $datas);
  }

  public function saveAnalysisChart($savePath, $font, $maxCount = 10, $fontSize = 14, $rawData = true) {
    if (!$savePath)
      return $this->log('錯誤的儲存路徑', '路徑：' . $savePath);

    if (!is_readable($font))
      return $this->log('參數錯誤', '字型檔案不存在或不可讀', 'Font：' . $font);

    if ($maxCount <= 0)
      return $this->log('參數錯誤', '參數 MaxCount 一定要大於 0', 'MaxCount：' . $maxCount);

    if ($fontSize <= 0)
      return $this->log('參數錯誤', '參數 FontSize 大小一定要大於 0', 'FontSize：' . $fontSize);

    $format = pathinfo($savePath, PATHINFO_EXTENSION);
    if (!$format || !in_array($format, static::$allows))
      return $this->log('不支援此檔案格式', 'Format：' . $format);

    if (!$datas = $this->getAnalysisDatas($maxCount))
      return $this->log('圖像分析錯誤');

    $newImage = new Imagick();

    foreach ($datas as $data) {
      $newImage->newImage(400, 20, new ImagickPixel('white'));

      $draw = new ImagickDraw();
      $draw->setFont($font);
      $draw->setFontSize($fontSize);
      $newImage->annotateImage($draw, 25, 14, 0, 'Percentage of total pixels : ' . (strlen($data['percent']) < 2 ? ' ':'') . $data['percent'] . '% (' . $data['count'] . ')');

      $tile = new Imagick();
      $tile->newImage(20, 20, new ImagickPixel('rgb(' . $data['color']['r'] . ',' . $data['color']['g'] . ',' . $data['color']['b'] . ')'));

      $newImage->compositeImage($tile, Imagick::COMPOSITE_OVER, 0, 0);
    }

    $newImage = $newImage->montageImage(new imagickdraw(), '1x' . count($datas) . '+0+0', '400x20+4+2>', Imagick::MONTAGEMODE_UNFRAME, '0x0+3+3');
    $newImage->setImageFormat($format);
    $newImage->setFormat($format);
    $newImage->writeImages($savePath, $rawData);

    return $this;
  }

  public function addFont($text, $font, $startX = 0, $startY = 12, $color = 'black', $fontSize = 12, $alpha = 1, $degree = 0) {
    if (!$text)
      return $this->log('沒有文字', '內容：' . $text);

    if (!is_readable($font))
      return $this->log('參數錯誤', '字型檔案不存在或不可讀', 'Font：' . $font);

    if ($startX < 0 || $startY < 0)
      return $this->log('起始點錯誤', '水平、垂直的起始點一定要大於 0', '水平點：' . $startX, '垂直點：' . $startY);

    if (!is_string($color))
      return $this->log('色碼格式錯誤，目前只支援字串 HEX 格式', '色碼：' . json_encode($color));

    if ($fontSize <= 0)
      return $this->log('參數錯誤', '參數 FontSize 大小一定要大於 0', 'FontSize：' . $fontSize);
    
    if ($alpha < 0 || $alpha > 1)
      return $this->log('參數錯誤', '參數 Alpha 一定要是 0 ~ 1', 'Alpha：' . $alpha);

    if (!is_numeric($degree))
      return $this->log('角度一定要是數字', 'Degree：' . $degree);

    $degree = $degree % 360;


    if (!$draw = $this->_createFont($font, $fontSize, $color, $alpha))
      return $this->log('Create 文字物件失敗');

    if ($this->format == 'gif') {
      $newImage = new Imagick();
      $newImage->setFormat($this->format);
      $imagick = clone $this->image;
      $imagick = $imagick->coalesceImages();
      
      do {
        $temp = new Imagick();
        $temp->newImage($this->dimension[0], $this->dimension[1], new ImagickPixel('transparent'));
        $temp->compositeImage($imagick, Imagick::COMPOSITE_DEFAULT, 0, 0);
        $temp->annotateImage($draw, $startX, $startY, $degree, $text);
        $newImage->addImage($temp);
        $newImage->setImageDelay($imagick->getImageDelay());
      } while ($imagick->nextImage());
    } else {
      $newImage = clone $this->image;
      $newImage->annotateImage($draw, $startX, $startY, $degree, $text);
    }

    return $this->_updateImage($newImage);
  }
}