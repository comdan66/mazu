<?php
echo $show->back();

// 標題, 欄位內容(string, function)
echo $show->text('ID', 'id')
          ->text('名稱', 'name')
          ->text('新增時間', 'createAt')
          ;
