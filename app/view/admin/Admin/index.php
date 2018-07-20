<?php
echo $list->search();

// 標題, 欄位內容(string, function), 寬度, 排序依據, 給予 class
echo $list->column('ID', 'id', 60, 'id')
          ->column('名稱', 'name')
          ->column('標籤', function($obj) { return array_map(function($role) { return '<span>' . \M\AdminRole::ROLE[$role->role] . '</span>'; }, $obj->roles); }, 200, null, 'items')
          ->column('新增時間', 'createAt', 150)
          ->column('編輯', function($obj, $column) {
            return $column->addShowLink(Url::toRouter('AdminAdminShow', $obj))
                          ->addEditLink(Url::toRouter('AdminAdminEdit', $obj))
                          ->addDeleteLink(Url::toRouter('AdminAdminDelete', $obj));
          }, 80, null, 'edit');

echo $list->pages();