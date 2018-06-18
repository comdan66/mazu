<?php

namespace _M;

class Inflector {
  private static $plural = ['/(quiz)$/i' => "$1zes", '/^(ox)$/i' => "$1en", '/([m|l])ouse$/i' => "$1ice", '/(matr|vert|ind)ix|ex$/i' => "$1ices", '/(x|ch|ss|sh)$/i' => "$1es", '/([^aeiouy]|qu)y$/i' => "$1ies", '/(hive)$/i' => "$1s", '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves", '/(shea|lea|loa|thie)f$/i' => "$1ves", '/sis$/i' => "ses", '/([ti])um$/i' => "$1a", '/(tomat|potat|ech|her|vet)o$/i' => "$1oes", '/(bu)s$/i' => "$1ses", '/(alias)$/i' => "$1es", '/(octop)us$/i' => "$1i", '/(cris|ax|test)is$/i' => "$1es", '/(us)$/i' => "$1es", '/s$/i' => "s", '/$/' => "s"];
  private static $singular = ['/(quiz)zes$/i' => "$1", '/(matr)ices$/i' => "$1ix", '/(vert|ind)ices$/i' => "$1ex", '/^(ox)en$/i' => "$1", '/(alias)es$/i' => "$1", '/(octop|vir)i$/i' => "$1us", '/(cris|ax|test)es$/i' => "$1is", '/(shoe)s$/i' => "$1", '/(o)es$/i' => "$1", '/(bus)es$/i' => "$1", '/([m|l])ice$/i' => "$1ouse", '/(x|ch|ss|sh)es$/i' => "$1", '/(m)ovies$/i' => "$1ovie", '/(s)eries$/i' => "$1eries", '/([^aeiouy]|qu)ies$/i' => "$1y", '/([lr])ves$/i' => "$1f", '/(tive)s$/i' => "$1", '/(hive)s$/i' => "$1", '/(li|wi|kni)ves$/i' => "$1fe", '/(shea|loa|lea|thie)ves$/i' => "$1f", '/(^analy)ses$/i' => "$1sis", '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis", '/([ti])a$/i' => "$1um", '/(n)ews$/i' => "$1ews", '/(h|bl)ouses$/i' => "$1ouse", '/(corpse)s$/i' => "$1", '/(us)es$/i' => "$1", '/(us|ss)$/i' => "$1", '/s$/i' => ""];
  private static $irregular = ['move' => 'moves','foot' => 'feet','goose' => 'geese','sex' => 'sexes','child' => 'children','man' => 'men','tooth' => 'teeth','person' => 'people'];
  private static $uncountable = ['sheep', 'fish', 'deer', 'series', 'species', 'money', 'rice', 'information', 'equipment'];

  // Book -> Books
  public static function pluralize($str) {
    if (in_array(strtolower($str), self::$uncountable))
      return $str;

    foreach (self::$irregular as $pattern => $result)
      if (preg_match('/' . $pattern . '$/i', $str))
        return preg_replace($pattern, $result, $str);

    foreach (self::$plural as $pattern => $result)
      if (preg_match($pattern, $str))
          return preg_replace($pattern, $result, $str);

    return $str;
  }

  // Books -> Book
  public static function singularize($str) {
    if (in_array(strtolower($str), self::$uncountable))
      return $str;

    foreach (self::$irregular as $result => $pattern)
      if (preg_match('/' . $pattern . '$/i', $str))
        return preg_replace($pattern, $result, $str);

    foreach (self::$singular as $pattern => $result)
      if (preg_match( $pattern, $str))
        return preg_replace($pattern, $result, $str);

    return $str;
  }

  // aa-AA -> aa_AA
  public static function underscorify($s) {
    return preg_replace(array('/[_\- ]+/', '/([a-z])([A-Z])/'), array('_','\\1_\\2'), trim($s));
  }

  // article -> article_id
  public static function keyify($className) {
    return strtolower(Inflector::underscorify(\M\denamespace($className))) . '_id';
  }

  // ModeName -> mode_names
  public static function tableize($s) {
    return self::pluralize(strtolower(Inflector::underscorify($s)));
  }

  // mode_names -> ModeName
  public static function untableize($s) {
    return ucfirst(Inflector::camelize(self::singularize($s)));
  }

  // mode_names -> modeNames
  public static function camelize($s) {
    $s = preg_replace('/[_-]+/', '_', trim($s));
    $s = str_replace(' ', '_', $s);

    $camelized = '';

    for ($i = 0, $n = strlen ($s); $i < $n; $i++)
      $camelized .= $s[$i] == '_' && $i+1 < $n ? strtoupper($s[++$i]) : $s[$i];

    $camelized = trim($camelized,' _');

    if (strlen($camelized) > 0)
      $camelized[0] = strtolower($camelized[0]);

    return $camelized;
  }

  // modeNames -> mode_names
  public static function uncamelize($s) {
    $normalized = '';

    for ($i = 0, $n = strlen($s); $i < $n; $i++)
      $normalized .= ctype_alpha($s[$i]) && strtoupper($s[$i]) === $s[$i] ? '_' . strtolower($s[$i]) : $s[$i];

    return trim($normalized,' _');
  }


  // 變數化
  public static function variablize($s) {
    return str_replace(array('-',' '),array('_','_'),strtolower(trim($s)));
  }
}
