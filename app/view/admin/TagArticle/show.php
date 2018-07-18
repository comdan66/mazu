<?php
echo $show->back();

// 標題, 欄位內容(string, function)
echo $show->text('ID', 'id')
          ->items('標籤', array_column(\M\toArray($obj->tags), 'name'))
          ->text('狀態', \M\Article::ENABLE[$obj->enable])
          ->image('封面', $obj->cover->url())
          ->text('標題', 'title')
          ->ckeditor('內容', 'content')
          ->text('新增時間', 'createAt')
          ;
