<?php
echo $list->search();

// 標題, 欄位內容(string, function), 寬度, 排序依據, 給予 class
echo $list->column('啟用', function($obj, $column) {
            return $column->setSwitcher([
              'on' => \M\Article::ENABLE_YES,
              'off' => \M\Article::ENABLE_NO,
              'url' => Url::base('admin/articles/' . $obj->id . '/enable'),
              'column' => 'enable',
              'cntlabel' => 'tag-enable'
            ]);
          }, 56, null, 'center')
          ->column('封面', function($obj) { return [$obj->cover->toImageTag()]; }, 50, null, 'oaips')
          ->column('ID', 'id', 60, 'id')
          ->column('標題', 'title', 200)
          ->column('內容', 'content')
          ->column('標籤', function($obj) { return array_map(function($tag) { return '<span>' . $tag->name . '</span>'; }, $obj->tags); }, 200, null, 'items')
          ->column('新增時間', 'createAt', 150)
          ->column('編輯', function($obj, $column) {
            return $column->addShowLink(Url::base('admin/articles/' . $obj->id))
                          ->addEditLink(Url::base('admin/articles/' . $obj->id . '/edit'))
                          ->addDeleteLink(Url::base('admin/articles/' . $obj->id));
          }, 80, null, 'edit');

echo $list->pages();