<?php

namespace M;

if (!function_exists('hasNamespace')) {
  function hasNamespace($className){
    return strpos($className, '\\') !== false;
  }
}
if (!function_exists('getNamespaces')) {
  function getNamespaces($className) {
    return hasNamespace($className) ? explode('\\', $className) : null;
  }
}

if (!function_exists('isHash')) {
  function isHash(&$arr) {
    if (!is_array($arr))
      return false;

    $keys = array_keys($arr);
    return @is_string($keys[0]) ? true : false;
  }
}
if (!function_exists('reverseOrder')) {
  function reverseOrder($order) {
    if (!trim($order))
      return $order;
    
    return implode(', ', array_map(function($part) {
      $v = trim(strtolower($part));
      return strpos($v,' asc') === false ? strpos($v,' desc') === false ? $v . ' DESC' : preg_replace('/desc/i', 'ASC', $v) : preg_replace('/asc/i', 'DESC', $v);
    }, explode(',', $order)));
  }
}

if (!function_exists('denamespace')) {
  function denamespace($class_name) {
    if (is_object($class_name))
      $class_name = get_class($class_name);

    if (hasNamespace($class_name)) {
      $parts = explode('\\', $class_name);
      return end($parts);
    }

    return $class_name;
  }
}

if (!function_exists ('modelsColumn')) {
  function modelsColumn ($arr, $key) {
    return array_map (function ($t) use ($key) {
      is_callable ($key) && $key = $key ();
      return $t->$key;
    }, $arr);
  }
}

if (!function_exists ('arrayFlatten')) {
  function arrayFlatten(array $array) {
    $i = 0;

    while ($i < count($array))
      if (is_array($array[$i]))
        array_splice($array,$i,1,$array[$i]);
      else
        ++$i;
    return $array;
  }
}

spl_autoload_register('\M\modelLoader', false, true);

function modelLoader($className) {
  if (!(count($namespaces = getNamespaces($className)) == 2 && in_array($namespace = array_shift($namespaces), ['M', '_M']) && ($modelName = array_shift($namespaces))))
    return false;

  \Load::path($tmp = PATH_SYS . 'model' . DIRECTORY_SEPARATOR . 'Where.php', ['找不到 Model 名稱為「Where」的檔案。', '載入路徑為：' . $tmp]);

  if ($namespace == 'M')
    \Load::path($tmp = PATH_MODEL . $modelName . '.php', ['找不到 Model 名稱為「' . $className . '」的物件。', '找不到 Model 名稱為「' . $modelName . '」的檔案。', '載入路徑為：' . $tmp]);
  else
    \Load::path($tmp = PATH_SYS . 'model' . DIRECTORY_SEPARATOR . $modelName . '.php', ['找不到 Model 名稱為「' . $className . '」的物件。', '找不到 Model 名稱為「' . $modelName . '」的檔案。', '載入路徑為：' . $tmp]);


  class_exists($className) || gg('找不到 Model 名稱為「' . $className . '」的物件。');
}

class Model extends \_M\Model {

}