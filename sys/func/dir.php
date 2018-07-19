<?php defined('MAZU') || exit('此檔案不允許讀取！');

if (!function_exists('dirMap')) {
  function dirMap($srcDir, $dirDepth = 0, $hidden = false) {
    if ($fp = @opendir($srcDir)) {
      $filedata = [];
      $new_depth = $dirDepth - 1;
      $srcDir = rtrim($srcDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

      while (false !== ($file = readdir($fp))) {
        if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.'))
          continue;

        is_dir($srcDir . $file) && $file .= DIRECTORY_SEPARATOR;

        if (($dirDepth < 1 || $new_depth > 0) && is_dir($srcDir . $file))
          $filedata[$file] = dirMap($srcDir . $file, $new_depth, $hidden);
        else
          $filedata[] = $file;
      }

      closedir($fp);
      return $filedata;
    }

    return false;
  }
}
