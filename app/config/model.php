<?php

return [
  'autoLoad' => true,

  'uploader' => [
    'tmpDir' => PATH_TMP,
    'baseDirs' => ['storage'],
    'baseUrl' => '',
    'thumbnail' => 'ThumbnailGd',

    'saveTool' => 'LocalSaveTool', // local s3
    
    'params' => [
      PATH,
    ],
    // 'S3SaveTool' => [
    //   'test.ioa.tw',
    //   '',
    //   '',
    // ]
  ]
];