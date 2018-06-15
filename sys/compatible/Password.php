<?php

if (isPhpVersion('5.5') || !defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH !== 1 || defined('HHVM_VERSION'))
  return;


defined ('PASSWORD_BCRYPT') || define ('PASSWORD_BCRYPT', 1);
defined ('PASSWORD_DEFAULT') || define ('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

if (!function_exists ('password_get_info')) {
  function password_get_info ($hash) {
    return strlen ($hash) < 60 || sscanf ($hash, '$2y$%d', $hash) !== 1
           ? array ('algo' => 0, 'algoName' => 'unknown', 'options' => array ())
           : array ('algo' => 1, 'algoName' => 'bcrypt', 'options' => array ('cost' => $hash));
  }
}

if (!function_exists ('password_hash')) {
  // $algo = PASSWORD_DEFAULT
  function password_hash ($password, $algo, array $options = array ()) {
    static $func_overload;
  
    isset ($func_overload) || $func_overload = (extension_loaded ('mbstring') && ini_get ('mbstring.func_overload'));

    if ($algo !== 1) {
      trigger_error ('password_hash(): Unknown hashing algorithm: ' . ((int) $algo), E_USER_WARNING);
      return null;
    }

    if (isset ($options['cost']) && ($options['cost'] < 4 || $options['cost'] > 31)) {
      trigger_error ('password_hash(): Invalid bcrypt cost parameter specified: ' . ((int)$options['cost']), E_USER_WARNING);
      return null;
    }

    if (isset ($options['salt']) && ($saltlen = ($func_overload ? mb_strlen ($options['salt'], '8bit') : strlen ($options['salt']))) < 22) {
      trigger_error ('password_hash(): Provided salt is too short: ' . $saltlen . ' expecting 22', E_USER_WARNING);
      return null;
    }

    if (!isset ($options['salt'])) {
      if (function_exists ('random_bytes')) {
        try {
          $options['salt'] = random_bytes(16);
        } catch (Exception $e) {
          Log::message ('compat/password: Error while trying to use random_bytes(): ' . $e->getMessage ());
          return false;
        }
      } else if (defined ('MCRYPT_DEV_URANDOM')) {
        $options['salt'] = mcrypt_create_iv (16, MCRYPT_DEV_URANDOM);
      } else if (DIRECTORY_SEPARATOR === '/' && (is_readable ($dev = '/dev/arandom') || is_readable ($dev = '/dev/urandom'))) {
        if (($fp = fopen ($dev, 'rb')) === false) {
          class_exists ('Log') && Log::message ('compat/password: Unable to open ' . $dev . ' for reading.');
          return false;
        }

        is_php ('5.4') && stream_set_chunk_size ($fp, 16);

        $options['salt'] = '';

        for ($read = 0; $read < 16; $read = ($func_overload) ? mb_strlen ($options['salt'], '8bit') : strlen ($options['salt'])) {
          if (($read = fread ($fp, 16 - $read)) === false) {
            class_exists ('Log') && Log::message ('compat/password: Error while reading from ' . $dev . '.');
            return false;
          }
          $options['salt'] .= $read;
        }

        fclose ($fp);
      } else if (function_exists ('openssl_random_pseudo_bytes')) {
        $is_secure = null;
        $options['salt'] = openssl_random_pseudo_bytes (16, $is_secure);
        
        if ($is_secure !== true) {
          class_exists ('Log') && Log::message ('compat/password: openssl_random_pseudo_bytes() set the $cryto_strong flag to false');
          return false;
        }
      } else {
        class_exists ('Log') && Log::message ('compat/password: No CSPRNG available.');
        return false;
      }

      $options['salt'] = str_replace ('+', '.', rtrim (base64_encode ($options['salt']), '='));
    } else if (!preg_match ('#^[a-zA-Z0-9./]+$#D', $options['salt'])) {
      $options['salt'] = str_replace ('+', '.', rtrim (base64_encode ($options['salt']), '='));
    }

    isset ($options['cost']) || $options['cost'] = 10;

    return (strlen ($password = crypt ($password, sprintf ('$2y$%02d$%s', $options['cost'], $options['salt']))) === 60) ? $password : false;
  }
}

if (!function_exists ('password_needs_rehash')) {
  function password_needs_rehash ($hash, $algo, array $options = array ()) {
    $info = password_get_info ($hash);

    if ($algo !== $info['algo'])
      return true;

    if ($algo === 1)
      return $info['options']['cost'] !== (isset ($options['cost']) ? (int) $options['cost'] : 10);

    return false;
  }
}

if (!function_exists ('password_verify')) {
  function password_verify ($password, $hash) {
    if (strlen ($hash) !== 60 || strlen ($password = crypt ($password, $hash)) !== 60)
      return false;

    $compare = 0;
    for ($i = 0; $i < 60; $i++)
      $compare |= (ord ($password[$i]) ^ ord ($hash[$i]));

    return $compare === 0;
  }
}
