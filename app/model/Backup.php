<?php

namespace M;

defined('MAZU') || exit('此檔案不允許讀取！');

class Backup extends Model {
  // static $hasOne = [];

  // static $hasMany = [];

  // static $belongToOne = [];

  // static $belongToMany = [];

  // static $uploaders = [];

  const TYPE_DB        = 'db';
  const TYPE_INFO      = 'info';
  const TYPE_ERROR     = 'error';
  const TYPE_WARNING   = 'warning';
  const TYPE_MODEL     = 'model';
  const TYPE_UPLOADER  = 'uploader';
  const TYPE_SAVE_TOOL = 'saveTool';
  const TYPE_THUMBNAIL = 'thumbnail';
  const TYPE_BENCHMARK = 'benchmark';
  const TYPE_QUERY     = 'query';
  const TYPE_OTHER     = 'other';

  const TYPE = [
    self::TYPE_DB        => '資料庫', 
    self::TYPE_INFO      => '訊息',
    self::TYPE_ERROR     => '錯誤',
    self::TYPE_WARNING   => '警告',
    self::TYPE_MODEL     => 'Model',
    self::TYPE_UPLOADER  => '上傳器',
    self::TYPE_SAVE_TOOL => '上傳工具',
    self::TYPE_THUMBNAIL => '縮圖',
    self::TYPE_BENCHMARK => '效能紀錄',
    self::TYPE_QUERY     => 'Query',
    self::TYPE_OTHER     => '其他',
  ];

  const STATUS_FAILURE = 'failure';
  const STATUS_SUCCESS = 'success';

  const STATUS = [
    self::STATUS_FAILURE => '失敗', 
    self::STATUS_SUCCESS => '成功',
  ];

  const UNWATCH_YES = 'yes';
  const UNWATCH_NO  = 'no';

  const UNWATCH = [
    self::UNWATCH_YES => '關閉', 
    self::UNWATCH_NO  => '打開',
  ];

  static $uploaders = [
    'file' => 'BackupFileFileUploader',
  ];

  public function putFiles($files) {
    foreach ($files as $key => $file)
      if (isset($this->$key) && $this->$key instanceof Uploader && !$this->$key->put($file))
        return false;
    return true;
  }
}

class BackupFileFileUploader extends FileUploader {}
