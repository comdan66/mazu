<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'matchIp' => false,

  'driver' => 'SessionFile',
  'params' => [
    'path' => PATH_SESSION
  ],

  // 'driver' => 'SessionDatabase',
  // 'params' => [
  //   'model' => '\M\SessionData'
  // ],

  // 'driver' => 'SessionRedis',
  // 'params' => [
  //   'prefix' => 'mazu_session:',
  //   'host' => 'localhost',
  //   'port' => '6379',
  //   'password' => null,
  //   'database' => null,
  //   'timeout' => null,
  // ],

  // 'driver' => 'SessionMemcached',
  // 'params' => [
  //   'prefix' => 'oaci_session:',
  //   'servers' => [
  //     ['host' => 'localhost', 'port' => '11211', 'weight' => 0],
  //   ],
  // ]

];