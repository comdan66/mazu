<?php
echo $form->back();

echo $form->rows([
  AdminForm::input()->title('名稱')->name('name')->d4($obj->name)->isNeed()->autofocus(),
  // AdminForm::textarea()->title('內容')->name('name')->d4('')->isNeed(),
  // AdminForm::ckeditor()->title('內容')->name('name')->d4('')->isNeed(),
  // AdminForm::image()->title('封面')->name('name')->src('')->isNeed(),
  // AdminForm::images()->title('圖片')->name('name')->srcs([])->isNeed(),
  // AdminForm::select()->title('圖片')->name('name')->d4('')->items(items(array_keys(\M\Tag::ENABLE), \M\Tag::ENABLE))->isNeed(),
  // AdminForm::radio()->title('圖片')->name('name')->d4(\M\Tag::ENABLE_YES)->items(items(array_keys(\M\Tag::ENABLE), \M\Tag::ENABLE))//->isNeed(),
  // AdminForm::switcher()->title('圖片')->name('name')->d4(\M\Tag::ENABLE_YES)->on(\M\Tag::ENABLE_YES)->off(\M\Tag::ENABLE_NO)->isNeed(),
  // AdminForm::checkbox()->title('圖片')->name('name')->d4s([])->items(items(array_keys(\M\Tag::ENABLE), \M\Tag::ENABLE))->isNeed(),
]);