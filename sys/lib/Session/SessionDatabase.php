<?php defined('MAZU') || exit('此檔案不允許讀取！');

class SessionDataModel extends M\Model {}

class SessionDatabase extends Session implements SessionHandlerInterface {
  private $model = null;
  private $rowExists = false;

  public function __construct() {
    parent::__construct();

    ini_set('session.save_path', 'SessionDataModel');
  }

  public function open($model, $name) {
    if ($this->model !== null)
      return $this->succ();

    $model && class_exists($model) || gg('SessionDatabase 錯誤，找不到指定的 Model，Model：' . $model);
    
    $obj = $this->query("SHOW TABLES LIKE '" . $model . "';")->fetch(PDO::FETCH_NUM);
    $obj && $obj[0] == $model || $this->runCreateSql($model) || gg('SessionDatabase 錯誤，產生 ' . $model . ' 資料表失敗！');

    $this->model = $model;
    return $this->succ();
  }

  public function read($sessionId) {
    $model = $this->model;

    if ($this->getLock($sessionId) !== false) {
      $this->sessionId = $sessionId;

      if (!$obj = $model::first(['select' => 'data', 'where' => $this->where($sessionId)])) {
        $this->rowExists = false;
        $this->fingerPrint = md5('');
        return '';
      }

      $result = $obj->data;
      $this->fingerPrint = md5($result);
      $this->rowExists = true;
      return $result;
    }
    

    $this->fingerPrint = md5('');
    return '';
  }

  public function write($sessionId, $sessionData) {
    $model = $this->model;

    if ($sessionId !== $this->sessionId) {
      if (!$this->releaseLock() || !$this->getLock($sessionId))
        return $this->fail();

      $this->rowExists = false;
      $this->sessionId = $sessionId;
    } else if ($this->lock === false) {
      return $this->fail();
    }

    if ($this->rowExists === false) {
      if ($model::create(['sessionId' => $sessionId, 'ipAddress' => Input::ip(), 'timestamp' => time(), 'data' => $sessionData])) {
        $this->fingerPrint = md5($sessionData);
        $this->rowExists = true;
        return $this->succ();
      }

      return $this->fail();
    }

    if (!$obj = $model::first(['select' => 'id, data, timestamp', 'where' => $this->where($sessionId)]))
      return $this->fail();

    $obj->timestamp = time();

    if ($this->fingerPrint !== md5($sessionData))
      $obj->data = $sessionData;

    if ($obj->save()) {
      $this->fingerPrint = md5($sessionData);
      return $this->succ();
    }

    return $this->fail();
  }

  public function close() {
    return ($this->lock && !$this->releaseLock()) ? $this->fail() : $this->succ();
  }

  public function destroy($sessionId) {
    $model = $this->model;

    if ($this->lock) {
      $obj = $model::first(['select' => 'id, data, timestamp', 'where' => $this->where($sessionId)]);

      if ($obj && !$obj->delete())
        return $this->fail();
    }

    if ($this->close() === $this->succ()) {
      $this->cookieDestroy();
      return $this->succ();
    }

    return $this->fail();
  }

  public function gc($maxLifeTime) {
    $model = $this->model;
    return $model::deleteAll(['where' => ['timestamp < ?', time() - $maxLifeTime]]) ? $this->succ() : $this->fail();
  }

  protected function getLock($sessionId) {
    $arg = md5($sessionId . (Session::matchIp() ? '_' . Input::ip() : ''));

    $obj = $this->query('SELECT GET_LOCK("' . $arg . '", 300) AS session_lock')->fetch();
    if (!($obj && $obj['session_lock']))
      return false;

    $this->lock = $arg;
    return true;
  }

  protected function releaseLock() {
    if (!$this->lock)
      return true;

    $obj = $this->query('SELECT RELEASE_LOCK("' . $this->lock . '") AS session_lock')->fetch();
    if (!($obj && $obj['session_lock']))
      return false;

    $this->lock = false;
    return true;
  }


  private function where($sessionId) {
    return Session::matchIp() ? ['sessionId = ? AND ipAddress = ?', $sessionId, Input::ip()] : ['sessionId = ?', $sessionId];
  }

  private function query($sql) {
    return \_M\Connection::instance()->query($sql);
  }

  private function runCreateSql($model) {
    $encoding = config('database', 'encoding');
    $dbcollat = $encoding ? $encoding . '_unicode_ci' : 'utf8mb4_unicode_ci';

    $sql = "CREATE TABLE `" . $model . "` ("
            . "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,"
            . "`sessionId` varchar(128) COLLATE " . $dbcollat . " NOT NULL DEFAULT '' COMMENT 'Session ID',"
            . "`ipAddress` varchar(45) COLLATE " . $dbcollat . " NOT NULL DEFAULT '' COMMENT 'IP',"
            . "`timestamp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Timestamp',"
            . "`data` blob NOT NULL COMMENT 'Data',"
            . "PRIMARY KEY (`id`),"
            . (Session::matchIp() ? "KEY `ipAddress_sessionId_index` (`ipAddress`,`sessionId`)" : "KEY `sessionId_index` (`sessionId`)")
          . ") ENGINE=InnoDB DEFAULT CHARSET=" . $encoding . " COLLATE=" . $dbcollat . ";";

    return $this->query($sql);
  }
}
