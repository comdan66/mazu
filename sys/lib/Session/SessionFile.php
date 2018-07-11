<?php defined('MAZU') || exit('此檔案不允許讀取！');

class SessionFile extends Session implements SessionHandlerInterface {
  const PERMISSIONS = 0777; //0600

  private $path;
  private $handle;
  private $fileNew;

  public function __construct() {
    parent::__construct();
    $this->path = null;
    $this->handle = null;
    $this->fileNew = true;

    ini_set('session.save_path', PATH_SESSION);
  }

  public function open($path, $name) {
    if ($this->path)
      return $this->succ();

    isset($path) && is_dir($path) && isReallyWritable($path) || gg('SessionFile 錯誤，路徑不存在或無法寫入，儲存路徑：' . $path);
    $this->path = $path . $name . '_' . (self::matchIp() ? Input::ip() . '_' : '');

    return $this->succ();
  }

  public function read($sessionId) {
    if ($this->handle === null) {
      $this->fileNew = !file_exists($this->path . $sessionId);

      if (($this->handle = fopen($this->path . $sessionId, 'c+b')) === false)
        return $this->fail();

      if (flock($this->handle, LOCK_EX) === false) {
        fclose($this->handle);
        $this->handle = null;
        return $this->fail();
      }

      $this->sessionId = $sessionId;

      if ($this->fileNew) {
        chmod($this->path . $sessionId, SessionFile::PERMISSIONS);
        $this->fingerPrint = md5('');
        return '';
      }
    } else if ($this->handle === false) {
      return $this->fail();
    } else {
      rewind($this->handle);
    }

    $data = '';
    for ($read = 0, $length = filesize($this->path . $sessionId); $read < $length; $read += charsetStrlen($buffer)) {
      if (($buffer = fread($this->handle, $length - $read)) === false)
        break;

      $data .= $buffer;
    }

    $this->fingerPrint = md5($data);
    return $data;
  }

  public function write($sessionId, $sessionData) {
    if ($sessionId !== $this->sessionId || $this->close() === $this->fail() || $this->read($sessionId) === $this->fail() || !is_resource($this->handle))
      return $this->fail();

    if ($this->fingerPrint === md5($sessionData))
      return !$this->fileNew && !touch($this->path . $sessionId) ? $this->fail() : $this->succ();

    if (!$this->fileNew) {
      ftruncate($this->handle, 0);
      rewind($this->handle);
    }

    if (($length = strlen($sessionData)) > 0) {
      for ($written = 0; $written < $length; $written += $result)
        if (($result = fwrite($this->handle, substr($sessionData, $written))) === false)
          break;

      if (!is_int($result)) {
        $this->fingerPrint = md5(substr($sessionData, 0, $written));
        return $this->fail();
      }
    }

    $this->fingerPrint = md5($sessionData);
    return $this->succ();
  }

  public function close() {
    if (is_resource($this->handle)) {
      flock($this->handle, LOCK_UN);
      fclose($this->handle);

      $this->handle = $this->fileNew = $this->sessionId = null;
    }

    return $this->succ();
  }

  public function destroy($sessionId) {
    if ($this->close() === $this->succ()) {
      if (file_exists($this->path . $sessionId)) {
        $this->cookieDestroy();

        return unlink($this->path . $sessionId) ? $this->succ() : $this->fail();
      }

      return $this->succ();
    }

    if ($this->path !== null) {
      clearstatcache();

      if (file_exists($this->path . $sessionId)) {
        $this->cookieDestroy();
        return unlink($this->path . $sessionId) ? $this->succ() : $this->fail();
      }

      return $this->succ();
    }

    return $this->fail();
  }

  public function gc($maxLifeTime) {
    if (($directory = opendir($this->path)) === false)
      return $this->fail();

    $ts = time() - $maxLifeTime;

    $pattern = (self::matchIp() === true) ? '[0-9a-f]{32}' : '';
    $pattern = sprintf('#\A%s' . $pattern . self::sessionIdRegexp() . '\z#', preg_quote(self::cookieName()));

    while (($file = readdir($directory)) !== false)
      if (preg_match($pattern, $file) && is_file($this->path . DIRECTORY_SEPARATOR . $file) && ($mtime = filemtime($this->path . DIRECTORY_SEPARATOR . $file)) !== false && $mtime <= $ts)
        unlink($this->path . DIRECTORY_SEPARATOR . $file);

    closedir($directory);
    return $this->succ();
  }
}
