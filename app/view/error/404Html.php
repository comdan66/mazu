<!DOCTYPE html>
<html lang="tw">
  <head>
    <meta http-equiv="Content-Language" content="zh-tw" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui" />

    <title>迷路惹</title>

    <style type="text/css">
      *,*:after,*:before{
        vertical-align:top;
        -moz-box-sizing:border-box;
        -webkit-box-sizing:border-box;
        box-sizing:border-box;
        -moz-osx-font-smoothing:antialiased;
        -webkit-font-smoothing:antialiased;
        -moz-font-smoothing:antialiased;
        -ms-font-smoothing:antialiased;
        -o-font-smoothing:antialiased
      }
      *::-moz-selection,*:after::-moz-selection,*:before::-moz-selection{
        color:#fff;
        background-color:#96c8ff
      }
      *::selection,*:after::selection,*:before::selection{
        color:#fff;
        background-color:#96c8ff
      }
      html{
        height:100%
      }
      html body{
        font-family:Arial, '微軟正黑體', 'Microsoft JhengHei', -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
        height:auto;
        text-align:center;
        margin:0;
        padding:0;
        font-size:medium;
        font-weight:normal;
        color:#555;
        background-color:#fff;
        background-color:#f9fafe;
        position:relative;
        display:inline-block;
        width:100%;
        min-height:100%
      }
      #main{
        position:fixed;
        left:0;
        top:calc(50% - 64px / 2);
        display:inline-block;
        width:100%;
        height:64px;
        line-height:32px;
        text-align:center;
        font-size:18px
      }
      #main a{
        display:inline;
        font-weight:normal;
        text-decoration:none;
        -moz-transition:color .3s,border-bottom .3s;
        -o-transition:color .3s,border-bottom .3s;
        -webkit-transition:color .3s,border-bottom .3s;
        transition:color .3s,border-bottom .3s;
        color:#4285f4
      }
      #main a.active,#main a:hover{
        color:#0d5bdd
      }
    </style>

  </head>
  <body lang="zh-tw">
    <main id='main'>
      迷路了嗎？
      <br>
      首頁在<a href='<?php echo Url::base('');?>'>這裡</a>喔！
    </main>
  </body>
</html>
