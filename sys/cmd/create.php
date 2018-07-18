<?php defined('MAZU') || exit('此檔案不允許讀取！');

if (!function_exists('headerText')) {
  function headerText($cho = null) {
    system('clear');

    echo "\n";
    echo ' ' . cliColor('【功能選項】', 'W') . "\n";
    echo cliColor($cho == '1' ? '  ➜' : '   ', 'y') . cliColor('  1. ', $cho == '1' ? 'Y' : null) . cliColor('新增 Migration', $cho == '1' ? 'Y' : null) . "\n";
    echo cliColor($cho == '2' ? '  ➜' : '   ', 'y') . cliColor('  2. ', $cho == '2' ? 'Y' : null) . cliColor('新增 Model', $cho == '2' ? 'Y' : null) . "\n";
    echo cliColor($cho == 'q' ? '  ➜' : '   ', 'y') . cliColor('  q. ', $cho == 'q' ? 'Y' : null) . cliColor('離開本程式～', $cho == 'q' ? 'Y' : null) . "\n";
  }
}

if (!function_exists('checkModelExist')) {
  function checkModelExist($name) {
    return $name !== null ? file_exists(PATH_MODEL . $name . '.php') : false;
  }
}

if (!function_exists('checkColumnHasDouble')) {
  function checkColumnHasDouble($c1, $c2) {
    if (!$c1)
      return false;
    
    if (!$c2)
      return false;

    foreach ($c2 as $c)
      if (in_array($c, $c1))
        return true;

    return false;
  }
}

if (!function_exists('createModel')) {
  function createModel($name, array $imgUploads, array $fileUploads) {
    $imgUploads = array_map(function($t) use($name) {return [$t, $name . ucfirst($t), 'ImageUploader']; }, $imgUploads);
    $fileUploads = array_map(function($t) use($name) { return [$t, $name . ucfirst($t), 'FileUploader']; }, $fileUploads);
    
    $uploader = implode("\n", array_map(function($uploader) { return "    '" . $uploader[0] . "' => '" . $uploader[1] . $uploader[2] . "',"; }, array_merge($imgUploads, $fileUploads)));
    $uploader = $uploader ? "  static \$uploaders = [\n" . $uploader . "\n  ];\n" . "  public function putFiles(\$files) {\n    foreach (\$files as \$key => \$file)\n      if (isset(\$this->\$key) && \$this->\$key instanceof Uploader && !\$this->\$key->put(\$file))\n        return false;\n    return true;\n  }\n" : "  // static \$uploaders = [];\n";

    $imgUploads = array_map(function($uploader) { return "class " . $uploader[1] . $uploader[2] . " extends " . $uploader[2] . " {\n  public function versions() {\n    return [\n      'w100' => ['resize' => [100, 100, 'width']],\n    ];\n  }\n}\n"; }, $imgUploads);
    $fileUploads = array_map(function($uploader) { return "class " . $uploader[1] . $uploader[2] . " extends " . $uploader[2] . " {}\n"; }, $fileUploads);
    $uploaders = array_merge($imgUploads, $fileUploads);
    
    return "<?php\n\nnamespace M;\n\ndefined('MAZU') || exit('此檔案不允許讀取！');\n\nclass " . $name . " extends Model {\n  // static \$hasOne = [];\n\n  // static \$hasMany = [];\n\n  // static \$belongToOne = [];\n\n  // static \$belongToMany = [];\n\n" . $uploader . "}\n" . ($uploaders ? "\n" . implode("\n", $uploaders) : '');
  }
}

if (!function_exists('createMigration')) {
  function createMigration($name, &$err = '') {
    // isReallyWritable(PATH_MIGRATION) || exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('您的 Migration 資料夾沒有讀寫權限。' . str_repeat(' ', CLI_LEN - 43), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");
    if (!isReallyWritable(PATH_MIGRATION)) {
      $err = '您的 Migration 資料夾沒有讀寫權限！';
      return false;
    }

    $files = array_keys(Migration::files());
    $version = $files ? end($files) + 1 : 1;

    // file_exists($path = PATH_MIGRATION . sprintf('%03s-%s.php', $version, $name)) && exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('您的 Migration 名稱重複！' . str_repeat(' ', CLI_LEN - 33), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");
    if (file_exists($path = PATH_MIGRATION . sprintf('%03s-%s.php', $version, $name))) {
      $err = '您的 Migration 名稱重複！';
      return false;
    }

    $content = "<?php defined('MAZU') || exit('此檔案不允許讀取！');\n\nreturn [\n  'up' => \"\",\n\n  'down' => \"\",\n\n  'at' => \"" . date('Y-m-d H:i:s') . "\"\n];\n";
    fileWrite($path, $content) || exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('您的 Migration 寫入失敗！' . str_repeat(' ', CLI_LEN - 33), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");

    $err = $path;
    return true;
  }
}

if (!function_exists('cho1')) {
  function cho1() {
    Load::sysFunc('file.php');
    Load::sysLib('Migration.php') || exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('Migration 初始化失敗。' . str_repeat(' ', CLI_LEN - 30), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");

    $check = $name = '';

    do {
      headerText('3');
      echo "\n " . cliColor('➜', 'R') . ' 請輸入要建立的檔名' . cliColor('(離開請按 control + c)', 'N') . '：' . ($name ? $name . "\n" : '');

      if ($name || ($name = trim(fgets(STDIN)))) {
        echo " " . cliColor('➜', 'R') . ' 檔名「' . cliColor($name, 'W') . '」是否正確' . cliColor('[y：沒錯, n：不是]', 'N') . '：';
        ($check = strtolower(trim(fgets(STDIN)))) == 'n' && $name = '';
      }
    } while ($check != 'y');
    
    exit( "\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n"
        . "\n " . cliColor('◎', 'G') . ' Migration「' . cliColor($name, 'W') . '」建立中.. ' . (createMigration($name, $err) ? cliColor('成功！', 'g')
        . "\n" . ' ' . cliColor('◎', 'G') . ' 已經成功建立 Migration：' . cliColor($err, 'W') . "\n" : cliColor('失敗！', 'r')
        . "\n" . ' ' . cliColor('◎', 'G') . ' 錯誤原因：' . cliColor($err, 'W') . "\n")
        . "\n");
  }
}

if (!function_exists('cho2')) {
  function cho2() {
    
    isReallyWritable(PATH_MODEL) || exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('您的 Model 資料夾沒有讀寫權限。' . str_repeat(' ', CLI_LEN - 39), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");

    do {
      $name = null;

      do {
        headerText('2');

        echo "\n";
        if ($r = checkModelExist($name)) {
          echo cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('Model 名稱「', null, 'r') . cliColor($name, 'W', 'r') . cliColor('」已經存在，請重新輸入！' .  str_repeat(' ', CLI_LEN - 44 - strlen($name)), null, 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n";
          $name = null;
        }

        echo ' ' . cliColor('➜', 'R') . ' 請輸入 Model 名稱' . cliColor('(離開請按 control + c)', 'N') . '：' . (!$r && $name ? $name . "\n" : '');
        
        $name = trim(fgets(STDIN));
      } while (!$name || checkModelExist($name));


      headerText('2');
      echo "\n " . cliColor('◎', 'G') . ' 請輸入 Model 名稱：' . cliColor($name, 'W')
         . "\n " . cliColor('➜', 'R') . ' 請輸入 ' . cliColor('圖片上傳器', 'W') . ' 欄位：';
      
      $imgUploads = trim(fgets(STDIN));
      $imgUploads = array_filter(array_unique(preg_split('/\s+/', $imgUploads)), function($t) { return $t !== ''; });


      headerText('2');
      echo "\n " . cliColor('◎', 'G') . ' 請輸入 Model 名稱：' . cliColor($name, 'W')
         . "\n " . cliColor('◎', 'G') . ' 請輸入 ' . cliColor('圖片上傳器', 'W') . ' 欄位：' . implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $imgUploads))
         . "\n " . cliColor('➜', 'R') . ' 請輸入 ' . cliColor('檔案上傳器', 'W') . ' 欄位：';
      
      $fileUploads = trim(fgets(STDIN));
      $fileUploads = array_filter(array_unique(preg_split('/\s+/', $fileUploads)), function($t) { return $t !== ''; });

      checkColumnHasDouble($imgUploads, $fileUploads) && exit("\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(' 警告！ ', 'Y', 'r') . cliColor('檔案上傳器有欄位與圖片上傳器欄位相衝突！ ', 'W', 'r') . cliColor(str_repeat(' ', CLI_LEN - 49), null, 'r') . "\n" . cliColor(str_repeat(' ', CLI_LEN), 'N', 'r') . "\n" . cliColor(str_repeat('─', CLI_LEN), 'W', 'r') . "\n\n");

      do {
        headerText('2');
        echo "\n " . cliColor('◎', 'G') . ' 請輸入 Model 名稱：' . cliColor($name, 'W')
           . "\n " . cliColor('◎', 'G') . ' 請輸入 ' . cliColor('圖片上傳器', 'W') . ' 欄位：' . implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $imgUploads))
           . "\n " . cliColor('◎', 'G') . ' 請輸入 ' . cliColor('檔案上傳器', 'W') . ' 欄位：' . implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $fileUploads));
        
        echo "\n\n " . cliColor('➜', 'R') . ' 以上資訊是否正確' . cliColor('[y：沒錯, n：重新填寫]', 'N') . '：';

        $fin = strtolower(trim(fgets(STDIN)));

      } while (!in_array($fin, ['y', 'n'], true));

    } while ($fin != 'y');
    

    headerText('2');
    echo "\n " . cliColor('◎', 'G') . ' 請輸入 Model 名稱：' . cliColor($name, 'W')
       . "\n " . cliColor('◎', 'G') . ' 請輸入 ' . cliColor('圖片上傳器', 'W') . ' 欄位：' . implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $imgUploads))
       . "\n " . cliColor('◎', 'G') . ' 請輸入 ' . cliColor('檔案上傳器', 'W') . ' 欄位：' . implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $fileUploads))
       . "\n";

    Load::sysFunc('file.php');
    $path = PATH_MODEL . $name . '.php';

    exit( "\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n"
        . "\n " . cliColor('➜', 'R') . " 新增 Model「" . cliColor($name, 'W') . "」- " . (fileWrite($path, createModel($name, $imgUploads, $fileUploads), 'x') ? cliColor('成功', 'g') : cliColor('失敗', 'r'))
        . "\n " . cliColor('➜', 'R') . " 位置：" . cliColor($path, 'W')
        . "\n " . cliColor('➜', 'R') . ' 圖片上傳器欄位：' . ($imgUploads ? implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $imgUploads)) : cliColor('無', 'N'))
        . "\n " . cliColor('➜', 'R') . ' 檔案上傳器欄位：' . ($fileUploads ? implode('、', array_map(function($t) { return cliColor($t, 'W'); }, $fileUploads)) : cliColor('無', 'N'))
        . "\n\n");
  }
}

if (Router::params(0) === 'migration') {
  cho1();
} else if (Router::params(0) === 'model') {
  cho2();
} else {
  do {
    headerText();
    echo "\n " . cliColor('➜', 'R') . ' 請輸入您的選項' .  cliColor('(q)', 'N') . '：';
    ($cho = strtolower(trim(fgets(STDIN)))) || $cho = 'q';
  } while (!in_array($cho, ['1', '2', 'q']));
}

$cho === '1' && cho1();
$cho === '2' && cho2();

headerText('q');
$cho === 'q' && exit("\n" . cliColor(str_repeat('═', CLI_LEN), 'N') . "\n\n" . "  好的！期待您下次再使用，" . cliColor('掰掰', 'W') . "～  \n\n");
