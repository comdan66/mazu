<?php
echo $list->search();

// 標題, 欄位內容(string, function), 寬度, 排序依據, 給予 class
echo $list->table()
          ->column('ID',  'id', 50, 'id')
          ->column('名稱', 'name')
          ->column('編輯', function($obj, $column) {
            return $column->addEditLink(Url::base('admin/tags/' . $obj . '/edit'))
                          ->addShowLink(Url::base('admin/tags/' . $obj . '/show'));
          }, 80, null, 'edit');

echo $list->pagination();