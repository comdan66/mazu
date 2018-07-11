<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Zip {
  private $now;
  private $zipdata = ''; 
  private $directory = ''; 
  private $entries = 0; 
  private $offset = 0; 
  private $fileNum = 0; 
  private $compressionLevel = 2; 

  public function __construct() {
    $this->now = time();
  }

  private function getModTime($dir) {
    $date = file_exists($dir) ? getdate(filemtime($dir)) : getdate($this->now);
    return ['mtime' => ($date['hours'] << 11) + ($date['minutes'] << 5) + $date['seconds'] / 2, 'mdate' => (($date['year'] - 1980) << 9) + ($date['mon'] << 5) + $date['mday']];
  }

  private function getZip() {
    return $this->entries !== 0 ? $this->zipdata . $this->directory . "\x50\x4b\x05\x06\x00\x00\x00\x00" . pack('v', $this->entries) . pack('v', $this->entries) . pack('V', charsetStrlen($this->directory)) . pack('V', charsetStrlen($this->zipdata)) . "\x00\x00" : false;
  }

  public function makeDir($directory) {
    preg_match('|.+/$|', $directory) || $directory .= '/';

    $dirTime = $this->getModTime($directory);
    $directory = str_replace('\\', '/', $directory);

    $this->zipdata   .= "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00" . pack('v', $dirTime['mtime']) . pack('v', $dirTime['mdate']) . pack('V', 0) . pack('V', 0) . pack('V', 0) . pack('v', charsetStrlen($directory)) . pack('v', 0) . $directory . pack('V', 0) . pack('V', 0) . pack('V', 0);
    $this->directory .= "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00" . pack('v', $dirTime['mtime']) . pack('v', $dirTime['mdate']) . pack('V',0) . pack('V',0) . pack('V',0) . pack('v', charsetStrlen($directory)) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('V', 16) . pack('V', $this->offset) . $directory;

    $this->offset = charsetStrlen($this->zipdata);
    $this->entries++;

    return $this;
  }

  public function setCompressionLevel($compressionLevel) {
    $this->compressionLevel = $compressionLevel;
    return $this;
  }
  public function addData($filepath, $data = null) {
    $fileTime = $this->getModTime($filepath);
    
    $filepath = str_replace('\\', '/', $filepath);
    $uncompressedSize = charsetStrlen($data);
    $crc32 = crc32($data);
    $gzdata = charsetSubstr(gzcompress($data, $this->compressionLevel), 2, -4);
    $compressedSize = charsetStrlen($gzdata);

    $this->zipdata   .= "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00" . pack('v', $fileTime['mtime']) . pack('v', $fileTime['mdate']) . pack('V', $crc32) . pack('V', $compressedSize) . pack('V', $uncompressedSize) . pack('v', charsetStrlen($filepath)) . pack('v', 0) . $filepath . $gzdata;
    $this->directory .= "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00" . pack('v', $fileTime['mtime']) . pack('v', $fileTime['mdate']) . pack('V', $crc32) . pack('V', $compressedSize) . pack('V', $uncompressedSize) . pack('v', charsetStrlen($filepath)) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('v', 0) . pack('V', 32) . pack('V', $this->offset) . $filepath;

    $this->offset = charsetStrlen($this->zipdata);
    $this->entries++;
    $this->fileNum++;
  
    return $this;
  }

  public function addFile($path, $archivePath = false) {
    return file_exists($path) && ($data = file_get_contents($path)) !== false ? $this->addData(is_string($archivePath) ? str_replace('\\', '/', $archivePath) : ($archivePath === false ? preg_replace('|.*/(.+)|', '\\1', str_replace('\\', '/', $path)) : str_replace('\\', '/', $path)), $data) : $this;
  }

  public function addDir($path, $includeHidden = true, $rootPath = null) {
    $path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    if (!$fp = @opendir($path))
      return false;

    $rootPath || $rootPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, dirname($path)) . DIRECTORY_SEPARATOR;

    while (false !== ($file = readdir($fp))) {
      if ($file === '.' || $file === '..' || ($includeHidden === false && $file[0] === '.'))
        continue;

      if (is_dir($path . $file) && $this->addDir($path . $file . DIRECTORY_SEPARATOR, $includeHidden, $rootPath))
        continue;
      
      if (is_file($path . $file) && $this->addFile($path . $file, str_replace($rootPath, '', str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path)) . $file))
        continue;
    }

    closedir($fp);
    return $this;
  }

  public function save($filepath) {
    if (!$fp = @fopen($filepath, FOPEN_READ_WRITE_CREATE_DESTRUCTIVE))
      return false;

    flock($fp, LOCK_EX);

    for ($result = $written = 0, $data = $this->getZip(), $length = charsetStrlen($data); $written < $length; $written += $result)
      if (($result = fwrite($fp, charsetSubstr($data, $written))) === FALSE)
        break;

    flock($fp, LOCK_UN);
    fclose($fp);

    return is_int($result);
  }

  public function clear() {
    $this->zipdata = '';
    $this->directory = '';
    $this->entries = 0;
    $this->fileNum = 0;
    $this->offset = 0;
    return $this;
  }
}
