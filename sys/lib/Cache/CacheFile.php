<?php defined('MAZU') || exit('此檔案不允許讀取！');

class CacheFile extends Cache {
  private $path = PATH_CACHE;

  public function __construct($options = []) {
    parent::__construct($options);

    $this->path = isset($options['path']) ? $options['path'] : PATH_CACHE;

    isReallyWritable($this->path) || gg ('CacheFile 錯誤，路徑無法寫入。');
    Load::sysFunc('file.php') || gg('載入 file 函式錯誤！');
  }

  public function get($id) {
    $data = $this->_get($id);
    return $data !== null ? is_array($data) ? $data['data'] : $data : null;
  }

  private function _get($id) {
    if (!is_file($this->path . $this->prefix . $id))
      return null;

    $data = unserialize(fileRead($this->path . $this->prefix . $id));

    if ($data['ttl'] <= 0 || time() <= $data['time'] + $data['ttl'])
      return $data;

    @unlink($this->path . $this->prefix . $id);
    return null;
  }

  public function save($id, $data, $ttl = 60) {
    $contents = [
      'time' => time(),
      'ttl' => $ttl,
      'data' => $data
    ];

    if (!fileWrite($this->path . $this->prefix . $id, serialize($contents)))
      return false;

    chmod($this->path . $this->prefix . $id, 0640);
    return true;
  }

  public function delete($id) {
    return is_file($this->path . $this->prefix . $id) ? @unlink($this->path . $this->prefix . $id) : false;
  }

  public function clean() {
    return filesDelete($this->path, false, true);
  }

  public function info() {
    return dirFilesInfo($this->path);
  }

  public function metadata($id) {
    if (!is_file($this->path . $this->prefix . $id))
      return null;

    $data = unserialize(file_get_contents($this->path . $this->prefix . $id));

    if (!is_array($data))
      return null;

    $mtime = filemtime($this->path . $this->prefix . $id);

    return !isset($data['ttl'], $data['time']) ? false : [
      'expire' => $data['time'] + $data['ttl'],
      'mtime'  => $mtime
    ];
  }

}
