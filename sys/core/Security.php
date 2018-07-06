<?php defined('MAZU') || exit('此檔案不允許讀取！');

class Security {
  private static $xssHash;
  private static $entities;
  private static $neverAllowedStr;
  private static $neverAllowedRegex;
  private static $naughtyTags;
  private static $evilAttributes;
  private static $filenameBadChars;

  public static function removeInvisibleCharacters($str, $urlEncoded = true) {
    $n = [];

    $urlEncoded && array_push($n, '/%0[0-8bcef]/i', '/%1[0-9a-f]/i', '/%7f/i');

    array_push($n, '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S');

    do {
      $str = preg_replace($n, '', $str, -1, $count);
    } while ($count);

    return $str;
  }

  public static function getRandomBytes($length) {
    if (!($length && is_numeric($length) && ctype_digit((string)$length)))
      return false;

    if (function_exists('random_bytes')) {
      try {
        return random_bytes((int)$length);
      } catch (Exception $e) {
        return false;
      }
    }

    if (defined('MCRYPT_DEV_URANDOM') && ($output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) !== false)
      return $output;

    if (is_readable('/dev/urandom') && ($fp = fopen('/dev/urandom', 'rb')) !== false) {
      stream_set_chunk_size($fp, $length);
      $output = fread($fp, $length);
      fclose($fp);
      
      if ($output !== false)
        return $output;
    }

    return function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes($length) : false;
  }

  public static function urldecodespaces($matches) {
    $input = $matches[0];
    $nospaces = preg_replace('#\s+#', '', $input);
    return $nospaces === $input ? $input : rawurldecode($nospaces);
  }

  public static function convertAttribute($match) {
    return str_replace(['>', '<', '\\'], ['&gt;', '&lt;', '\\\\'], $match[0]);
  }

  public static function xssHash() {
    if (self::$xssHash !== null)
      return self::$xssHash;

    $rand = self::getRandomBytes(16);
    return self::$xssHash = $rand === false ? md5(uniqid(mt_rand(), true)) : bin2hex($rand);
  }

  public static function entityDecode($str) {
    if (strpos($str, '&') === false)
      return $str;

    $flag = ENT_COMPAT | ENT_HTML5;

    if (!self::$entities) {
      self::$entities = array_map('strtolower', get_html_translation_table(HTML_ENTITIES, $flag, 'UTF-8'));

      if ($flag === ENT_COMPAT) {
        self::$entities[':']  = '&colon;';
        self::$entities['(']  = '&lpar;';
        self::$entities[')']  = '&rpar;';
        self::$entities["\n"] = '&NewLine;';
        self::$entities["\t"] = '&Tab;';
      }
    }

    do {
      $strCompare = $str;

      if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
        $replace = [];
        $matches = array_unique(array_map('strtolower', $matches[0]));

        foreach ($matches as &$match)
          if (($char = array_search($match . ';', self::$entities, true)) !== false)
            $replace[$match] = $char;

        $str = str_replace(array_keys($replace), array_values($replace), $str);
      }

      $str = html_entity_decode(preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str), $flag, 'UTF-8');
      $flag === ENT_COMPAT && $str = str_replace(array_values(self::$entities), array_keys(self::$entities), $str);
    } while ($strCompare !== $str);

    return $str;
  }

  public static function decodeEntity($match) {
    return str_replace(self::xssHash(), '&', self::entityDecode(preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', self::xssHash() . '\\1=\\2', $match[0])));
  }

  private static function doNeverAllowed($str) {
    self::$neverAllowedStr   || self::$neverAllowedStr   = ['document.cookie' => '[removed]', 'document.write' => '[removed]', '.parentNode' => '[removed]', '.innerHTML' => '[removed]', '-moz-binding' => '[removed]', '<!--' => '&lt;!--', '-->' => '--&gt;', '<![CDATA[' => '&lt;![CDATA[', '<comment>' => '&lt;comment&gt;', '<%' => '&lt;&#37;'];
    self::$neverAllowedRegex || self::$neverAllowedRegex = ['javascript\s*:', '(document|(document\.)?window)\.(location|on\w*)', 'expression\s*(\(|&\#40;)', 'vbscript\s*:', 'wscript\s*:', 'jscript\s*:', 'vbs\s*:', 'Redirect\s+30\d', "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"];

    $str = str_replace(array_keys(self::$neverAllowedStr), self::$neverAllowedStr, $str);
    foreach (self::$neverAllowedRegex as $regex)
      $str = preg_replace('#' . $regex . '#is', '[removed]', $str);

    return $str;
  }
  
  public static function compactExplodedWords($matches) {
    return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
  }

  public static function filterAttributes($str) {
    $out = '';
    if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
      foreach ($matches[0] as $match)
        $out .= preg_replace('#/\*.*?\*/#s', '', $match);

    return $out;
  }

  public static function jsLinkRemoval($match) {
    return str_replace($match[1], preg_replace('#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|d\s*a\s*t\s*a\s*:)#si', '', self::filterAttributes($match[1])), $match[0]);
  }

  public static function jsImgRemoval($match) {
    return str_replace($match[1], preg_replace('#src=.*?(?:(?:alert|prompt|confirm|eval)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si', '', self::filterAttributes($match[1])), $match[0]);
  }

  public static function sanitizeNaughtyHtml($matches) {
    self::$naughtyTags    || self::$naughtyTags    = ['alert', 'area', 'prompt', 'confirm', 'applet', 'audio', 'basefont', 'base', 'behavior', 'bgsound', 'blink', 'body', 'embed', 'expression', 'form', 'frameset', 'frame', 'head', 'html', 'ilayer', 'iframe', 'input', 'button', 'select', 'isindex', 'layer', 'link', 'meta', 'keygen', 'object', 'plaintext', 'style', 'script', 'textarea', 'title', 'math', 'video', 'svg', 'xml', 'xss'];
    self::$evilAttributes || self::$evilAttributes = ['on\w+', 'style', 'xmlns', 'formaction', 'form', 'xlink:href', 'FSCommand', 'seekSegmentTime'];

    if (empty($matches['closeTag']))
      return '&lt;' . $matches[1];

    if (in_array(strtolower($matches['tagName']), self::$naughtyTags, true))
      return '&lt;' . $matches[1] . '&gt;';

    if (isset($matches['attributes'])) {
      $attributes = [];
      $attributes_pattern = '#' . '(?<name>[^\s\042\047>/=]+)' . '(?:\s*=(?<value>[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))' . '#i';
      $is_evil_pattern = '#^(' . implode('|', self::$evilAttributes) . ')$#i';

      do {
        $matches['attributes'] = preg_replace('#^[^a-z]+#i', '', $matches['attributes']);

        if (!preg_match($attributes_pattern, $matches['attributes'], $attribute, PREG_OFFSET_CAPTURE))
          break;

        array_push($attributes, preg_match($is_evil_pattern, $attribute['name'][0]) || (trim ($attribute['value'][0]) === '') ? 'xss=removed' : $attribute[0][0]);

        $matches['attributes'] = substr($matches['attributes'], $attribute[0][1] + strlen($attribute[0][0]));
      } while ($matches['attributes'] !== '');

      $attributes = empty($attributes) ? '' : ' ' . implode(' ', $attributes);

      return '<' . $matches['slash'] . $matches['tagName'] . $attributes . '>';
    }

    return $matches[0];
  }

  public static function xssClean($str, $isImage = false) {
    if (is_array($str)) {
      foreach ($str as $key => &$value)
        $str[$key] = self::xssClean($value);
      return $str;
    }

    $str = self::removeInvisibleCharacters($str);

    if (stripos($str, '%') !== false) {
      do {
        $oldStr = $str;
        $str = rawurldecode($str);
        $str = preg_replace_callback('#%(?:\s*[0-9a-f]){2,}#i', ['Security', 'urldecodespaces'], $str);
      } while ($oldStr !== $str);
      unset ($oldStr);
    }

    $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", ['Security', 'convertAttribute'], $str);
    $str = preg_replace_callback('/<\w+.*/si', ['Security', 'decodeEntity'], $str);
    $str = self::removeInvisibleCharacters($str);
    $str = str_replace("\t", ' ', $str);

    $convertedStr = $str;

    $str = self::doNeverAllowed($str);

    $str = $isImage === true ? preg_replace('/<\?(php)/i', '&lt;?\\1', $str) : str_replace(['<?', '?'.'>'], ['&lt;?', '?&gt;'], $str);

    $words = ['javascript', 'expression', 'vbscript', 'jscript', 'wscript', 'vbs', 'script', 'base64', 'applet', 'alert', 'document', 'write', 'cookie', 'window', 'confirm', 'prompt', 'eval'];

    foreach ($words as $word) {
      $word = implode('\s*', str_split($word)) . '\s*';
      $str = preg_replace_callback('#(' . substr($word, 0, -3) . ')(\W)#is', ['Security', 'compactExplodedWords'], $str);
    }

    do {
      $original = $str;

      preg_match('/<a/i', $str) && $str = preg_replace_callback('#<a(?:rea)?[^a-z0-9>]+([^>]*?)(?:>|$)#si', ['Security', 'jsLinkRemoval'], $str);

      preg_match('/<img/i', $str) && $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', ['Security', 'jsImgRemoval'], $str);

      preg_match('/script|xss/i', $str) && $str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);
    } while ($original !== $str);

    unset ($original);

    $pattern = '#<((?<slash>/*\s*)((?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)|.+)[^\s\042\047a-z0-9>/=]*(?<attributes>(?:[\s\042\047/=]*[^\s\042\047>/=]+(?:\s*=(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))?)*)[^>]*)(?<closeTag>\>)?#isS';

    do {
      $oldStr = $str;
      $str = preg_replace_callback($pattern, ['Security', 'sanitizeNaughtyHtml'], $str);
    } while ($oldStr !== $str);
    unset($oldStr);

    $str = self::doNeverAllowed(preg_replace('#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', '\\1\\2&#40;\\3&#41;', $str));

    return $isImage === true ? $str === $convertedStr : $str;
  }

  public static function sanitizeFilename($str, $relativePath = false) {
    $bad = ['../', '<!--', '-->', '<', '>', "'", '"', '&', '$', '#', '{', '}', '[', ']', '=', ';', '?', '%20', '%22', '%3c', '%253c', '%3e', '%0e', '%28', '%29', '%2528', '%26', '%24', '%3f', '%3b', '%3d'];

    $relativePath || array_push($bad, './', '/');

    $str = self::removeInvisibleCharacters($str, false);

    do {
      $oldStr = $str;
      $str = str_replace($bad, '', $str);
    } while ($oldStr !== $str);

    return stripslashes($str);
  }

  public static function stripImageTags($str) {
    return preg_replace(['#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i', '#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i'], '\\2', $str);
  }
}
