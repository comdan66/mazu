<?php

return [
  'base_url' => '',
  'tmp_dir' => PATH_TMP,

  'driver' => 's3', // local s3

  's3' => [
    'bucket' => 'test.ioa.tw',
    'access' => '',
    'secret' => '',
    'base_dirs' => ['storage'],
  ],
  'local' => [
    'base_dirs' => ['storage'],
  ]
];