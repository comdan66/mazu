<?php
echo $list->search();

// 標題, 欄位內容(string, function), 寬度, 排序依據, 給予 class
echo $list->column('已讀', function($obj, $column) {
            return $column->setSwitcher([
              'on' => \M\Backup::UNWATCH_YES,
              'off' => \M\Backup::UNWATCH_NO,
              'url' => Url::toRouter('AdminBackupUnwatch', $obj),
              'column' => 'unwatch',
              'cntlabel' => 'backup-unwatch'
            ]);
          }, 56, null, 'center')
          ->column('ID', 'id', 60, 'id')
          ->column('類型', function($obj) { return \M\Backup::TYPE[$obj->type]; })
          ->column('大小', function($obj) { return number_format($obj->size) . ' Byte'; }, 120, 'size')
          ->column('狀態', function($obj) { return $obj->status != \M\Backup::STATUS_SUCCESS ? '<font color="red">' . \M\Backup::STATUS[$obj->status] . '</font>' : \M\Backup::STATUS[$obj->status]; }, 80, 'status')
          ->column('新增時間', 'createAt', 150)
          ->column('編輯', function($obj, $column) {
            return $column->addShowLink(Url::toRouter('AdminTagShow', $obj));
          }, 44, null, 'edit');

echo $list->pages();