<?php
echo $form->back();

echo $form->rows([
  AdminForm::switcher()->title('狀態')->name('enable')->d4(\M\Article::ENABLE_YES)->on(\M\Article::ENABLE_YES)->off(\M\Article::ENABLE_NO)->isNeed(),
  AdminForm::image()->title('封面')->name('cover')->src('')->isNeed(),
  AdminForm::checkbox()->title('標籤')->name('tagIds')->d4s([])->items(items(array_column($tags, 'id'), array_column($tags, 'name')))->isNeed(),
  AdminForm::input()->title('標題')->name('title')->d4('')->isNeed()->autofocus(),
  AdminForm::ckeditor()->title('內容')->name('content')->d4('')->isNeed(),
]);