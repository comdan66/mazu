<?php
echo $form->back();

echo $form->rows([
  AdminForm::switcher()->title('狀態')->name('enable')->d4($obj->enable)->on(\M\Article::ENABLE_YES)->off(\M\Article::ENABLE_NO)->isNeed(),
  AdminForm::image()->title('封面')->name('cover')->src($obj->cover->url())->isNeed(),
  AdminForm::images()->title('組圖')->name('images')->srcs(array_map(function($image) { return $image->pic; }, $obj->images))->isNeed(),
  AdminForm::checkbox()->title('標籤')->name('tagIds')->d4s(arrayColumn($obj->tags, 'id'))->items(items(arrayColumn($tags, 'id'), arrayColumn($tags, 'name')))->isNeed(),
  AdminForm::input()->title('標題')->name('title')->d4($obj->title)->isNeed()->autofocus(),
  AdminForm::ckeditor()->title('內容')->name('content')->d4($obj->content)->isNeed(),
]);