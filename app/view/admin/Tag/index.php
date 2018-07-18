<?php
echo $list->search();

// 標題, 欄位內容(string, function), 寬度, 排序依據, 給予 class
echo $list->column('ID', 'id', 60, 'id')
          ->column('名稱', 'name')
          ->column('文章', function($obj, $column) {
            return $column->addLink(Url::toRouter('AdminTagArticleIndex', $obj), [], number_format(count($obj->articles)) . ' 篇');
          }, 100)
          ->column('新增時間', 'createAt', 150)
          ->column('編輯', function($obj, $column) {
            return $column->addShowLink(Url::toRouter('AdminTagShow', $obj))
                          ->addEditLink(Url::toRouter('AdminTagEdit', $obj))
                          ->addDeleteLink(Url::toRouter('AdminTagDelete', $obj));
          }, 80, null, 'edit');

echo $list->pages();