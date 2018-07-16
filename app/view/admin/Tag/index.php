<?php
echo $list->search();

// 標題, 欄位內容(string, function), 寬度, 排序依據, 給予 class
echo $list->column('狀態', function($obj, $column) {
            return $column->setSwitcher([
              'on' => \M\Tag::ENABLE_YES,
              'off' => \M\Tag::ENABLE_NO,
              'url' => Url::base('admin/tags/' . $obj->id . '/enable'),
              'column' => 'enable',
              'cntlabel' => 'tag-enable'
            ]);
          }, 56, null, 'center')
          ->column('ID', 'id', 60, 'id')
          ->column('名稱', 'name')
          ->column('新增時間', 'createAt', 150)
          ->column('編輯', function($obj, $column) {
            return $column->addShowLink(Url::base('admin/tags/' . $obj->id))
                          ->addEditLink(Url::base('admin/tags/' . $obj->id . '/edit'))
                          ->addDeleteLink(Url::base('admin/tags/' . $obj->id));
          }, 80, null, 'edit');

echo $list->pages();