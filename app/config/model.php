<?php defined('MAZU') || exit('此檔案不允許讀取！');

return [
  'autoLoad' => true,

  'uploader' => [
    'tmpDir' => PATH_TMP,
    'baseDirs' => ['storage'],
    'baseUrl' => '/',
    'thumbnail' => 'ThumbnailImagick', // Imagick 、 Gd

    'saveTool' => 'SaveToolLocal',
    'params' => [
      PATH,
    ],
    
    // 'saveTool' => 'SaveToolS3',
    // 'params' => [
    //   'bucket',
    //   'accessKey',
    //   'secretKey',
    // ],
  ]
];