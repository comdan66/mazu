<?php
echo $show->back();

// 標題, 欄位內容(string, function)
echo $show->text('ID', 'id')
          ->text('名稱', 'name')
          ->items('角色', array_map(function($role) { return \M\AdminRole::ROLE[$role->role]; }, $obj->roles))
          ->text('新增時間', 'createAt')
          ;
