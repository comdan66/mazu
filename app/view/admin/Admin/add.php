<?php
echo $form->back();

echo $form->rows([
  AdminForm::input()->title('名稱')->name('name')->d4('')->isNeed()->autofocus(),
  AdminForm::input()->title('帳號')->name('account')->d4('')->isNeed(),
  AdminForm::input('password')->title('密碼')->name('pwd')->d4('')->isNeed(),
  AdminForm::checkbox()->title('角色')->name('roles')->d4s([])->items(items(array_keys(\M\AdminRole::ROLE), \M\AdminRole::ROLE))->isNeed(),
]);