<?php defined('MAZU') || exit('此檔案不允許讀取！');

if (!function_exists('fileRead')) {
  function fileRead($file) {
    if (!file_exists($file))
      return false;

    if (function_exists('file_get_contents'))
      return @file_get_contents($file);

    $fp = @fopen($file, FOPEN_READ);
    
    if ($fp === false)
      return false;

    flock($fp, LOCK_SH);

    $data = '';
    if (filesize($file) > 0)
      $data =& fread($fp, filesize($file));

    flock($fp, LOCK_UN);
    fclose($fp);

    return $data;
  }
}

if (!function_exists('fileWrite')) {
  function fileWrite($path, $data, $mode = 'wb') {
    if (function_exists('file_put_contents'))
      return @file_put_contents($path, $data);

    if (!$fp = @fopen($path, $mode))
      return false;

    flock($fp, LOCK_EX);

    for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
      if (($result = fwrite($fp, substr($data, $written))) === false)
        break;

    flock($fp, LOCK_UN);
    fclose($fp);

    return is_int($result);
  }
}

if (!function_exists('filesDelete')) {
  function filesDelete($path, $delDir = false, $htdocs = false, $level = 0) {
    $path = rtrim($path, '/\\');

    if (!$currentDir = @opendir($path))
      return false;

    while (false !== ($filename = @readdir($currentDir))) {
      if ($filename !== '.' && $filename !== '..') {
        $filepath = $path . DIRECTORY_SEPARATOR . $filename;

        if (is_dir($filepath) && $filename[0] !== '.' && !is_link($filepath))
          filesDelete($filepath, $delDir, $htdocs, $level + 1);
        elseif ($htdocs !== true || !preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
          @unlink($filepath);
        else;
      }
    }

    closedir($currentDir);

    return $delDir === true && $level > 0 ? @rmdir($path) : true;
  }
}

if (!function_exists ('dirFilesInfo')) {
  function dirFilesInfo($sourceDir, $topLevelOnly = true, $recursion = false) {
    static $filedata = [];
    $relativePath = $sourceDir;

    if ($fp = @opendir($sourceDir)) {
      if ($recursion === false) {
        $filedata = [];
        $sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      }

      while (false !== ($file = readdir($fp))) {
        if (is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === false) {
          dirFilesInfo($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, true);
        } elseif ($file[0] !== '.') {
          $filedata[$file] = fileInfo($sourceDir . $file);
          $filedata[$file]['relative_path'] = $relativePath;
        }
      }

      closedir($fp);
      return $filedata;
    }

    return false;
  }
}

if (!function_exists('fileInfo')) {
  function fileInfo($file, $returnedVals = ['name', 'server_path', 'size', 'date']) {
    if (!file_exists($file))
      return false;

    if (is_string($returnedVals))
      $returnedVals = explode(',', $returnedVals);

    $fileinfo = false;
    foreach ($returnedVals as $key)
      switch ($key) {
        case 'name':        $fileinfo['name']        = basename($file); break;
        case 'server_path': $fileinfo['server_path'] = $file; break;
        case 'size':        $fileinfo['size']        = filesize($file); break;
        case 'date':        $fileinfo['date']        = filemtime($file); break;
        case 'readable':    $fileinfo['readable']    = is_readable($file); break;
        case 'writable':    $fileinfo['writable']    = isReallyWritable($file); break;
        case 'executable':  $fileinfo['executable']  = is_executable($file); break;
        case 'fileperms':   $fileinfo['fileperms']   = fileperms($file); break;
      }

    return $fileinfo;
  }
}
