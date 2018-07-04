<!DOCTYPE html>
<html lang="tw">
  <head>
    <meta http-equiv="Content-Language" content="zh-tw" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui" />

    <title>錯誤</title>

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
        min-height:100%
      }
      html body{
        position:relative;
        display:inline-block;
        width:100%;
        min-height:100%;
        margin:0;
        padding:0;
        color:#5a5a5a;
        text-align:center;
        font-size:medium;
        font-family:Roboto, RobotoDraft, Helvetica, Arial, sans-serif, "微軟正黑體", "Microsoft JhengHei";
        background:#f0f0f0;
        -moz-osx-font-smoothing:antialiased;
        -webkit-font-smoothing:antialiased;
        -moz-font-smoothing:antialiased;
        -ms-font-smoothing:antialiased;
        -o-font-smoothing:antialiased
      }
      a{
        display:inline;
        font-weight:normal;
        text-decoration:none;
        -moz-transition:color .3s,border-bottom .3s;
        -o-transition:color .3s,border-bottom .3s;
        -webkit-transition:color .3s,border-bottom .3s;
        transition:color .3s,border-bottom .3s;
        color:#4285f4;
        border-bottom:1px solid #4285f4;
        border-bottom-color:rgba(70,136,241,0.4);
        border-bottom-style:dashed;
        -moz-transition:border-bottom-color .3s,color .3s;
        -o-transition:border-bottom-color .3s,color .3s;
        -webkit-transition:border-bottom-color .3s,color .3s;
        transition:border-bottom-color .3s,color .3s
      }
      a.active,a:hover{
        color:#0d5bdd;
        border-bottom:1px solid #0d5bdd
      }
      a:hover{
        border-bottom-color:rgba(70,136,241,0.8);
        border-bottom-style:dashed
      }
      #main{
        display:inline-block;
        width:100%
      }
      #main>div{
        display:inline-block;
        width:100%;
        max-width:960px;
        background-color:white;
        padding:20px 32px;
        text-align:left;
        -moz-border-radius:2px;
        -webkit-border-radius:2px;
        border-radius:2px;
        -moz-box-shadow:0 0 1px #c8c8c8,1px 1px 1px #c8c8c8;
        -webkit-box-shadow:0 0 1px #c8c8c8,1px 1px 1px #c8c8c8;
        box-shadow:0 0 1px #c8c8c8,1px 1px 1px #c8c8c8;
        margin-top:16px;
        margin-bottom:16px
      }
      @media screen and (max-width: 959px) and (min-width: 0){
        #main>div{
            margin-bottom:0;
            margin-top:0
        }
      }
      @media screen and (max-width: 749px) and (min-width: 0){
        #main>div{
            padding:20px
        }
      }
      @media screen and (max-width: 499px) and (min-width: 0){
        #main>div{
            padding:12px
        }
      }
      #main>div>h1,#main>div>h2,#main>div>h3{
        position:relative;
        font-size:20px;
        display:inline-block;
        width:100%;
        height:32px;
        line-height:32px;
        margin:0;
        margin-top:4px
      }
      #main>div>h1:not(:first-child),#main>div>h2:not(:first-child),#main>div>h3:not(:first-child){
        margin-top:32px
      }
      #main>div>h1:before,#main>div>h2:before,#main>div>h3:before{
        position:absolute;
        left:0;
        top:0;
        display:none;
        width:32px;
        line-height:32px;
        height:32px;
        text-align:center;
        color:#969682;
        font-weight:bold
      }
      #main>div>h1:after,#main>div>h2:after,#main>div>h3:after{
        display:none;
        width:100%;
        height:14px;
        line-height:14px;
        font-size:13px;
        font-weight:normal;
        color:#828282
      }
      #main>div>h1[data-icon]:not([data-icon=""]),#main>div>h2[data-icon]:not([data-icon=""]),#main>div>h3[data-icon]:not([data-icon=""]){
        padding-left:48px
      }
      #main>div>h1[data-icon]:not([data-icon=""]):before,#main>div>h2[data-icon]:not([data-icon=""]):before,#main>div>h3[data-icon]:not([data-icon=""]):before{
        display:inline-block;
        content:attr(data-icon);
        -moz-transform:rotate(0);
        -ms-transform:rotate(0);
        -webkit-transform:rotate(0);
        transform:rotate(0);
        font-size:32px;
        line-height:30px
      }
      #main>div>h1[data-icon]:not([data-icon=""])[data-msg]:not([data-msg=""]),#main>div>h2[data-icon]:not([data-icon=""])[data-msg]:not([data-msg=""]),#main>div>h3[data-icon]:not([data-icon=""])[data-msg]:not([data-msg=""]){
        padding-left:64px
      }
      #main>div>h1[data-msg]:not([data-msg=""]),#main>div>h2[data-msg]:not([data-msg=""]),#main>div>h3[data-msg]:not([data-msg=""]){
        height:48px
      }
      #main>div>h1[data-msg]:not([data-msg=""]):before,#main>div>h2[data-msg]:not([data-msg=""]):before,#main>div>h3[data-msg]:not([data-msg=""]):before{
        width:48px;
        line-height:50px;
        height:48px;
        font-size:48px
      }
      #main>div>h1[data-msg]:not([data-msg=""]):after,#main>div>h2[data-msg]:not([data-msg=""]):after,#main>div>h3[data-msg]:not([data-msg=""]):after{
        display:inline-block;
        content:attr(data-msg)
      }
      #main>div>h2{
        font-size:18px
      }
      #main>div>h3{
        font-size:16px
      }
      #main>div>i{
        display:block;
        width:calc(100% + 32px * 2);
        margin-left:-32px;
        height:1px;
        margin-top:20px;
        margin-bottom:32px;
        border-top:1px solid #e6e6e6
      }
      @media screen and (max-width: 749px) and (min-width: 0){
        #main>div>i{
            width:calc(100% + 10px * 2);
            margin-left:-10px
        }
      }
      @media screen and (max-width: 499px) and (min-width: 0){
        #main>div>i{
            width:calc(100% + 6px * 2);
            margin-left:-6px
        }
      }
      #main>div>i+*{
        margin-top:0 !important
      }
      #main>div>i+i{
        display:none
      }
      #main>div>blockquote{
        display:inline-block;
        width:100%;
        margin:0;
        padding:8px 16px;
        border-left:3px solid #c8c8c8;
        margin-top:32px;
        color:#787878
      }
      #main>div>blockquote pre{
        display: inline-block;
        width: 100%;
        overflow-x: auto;
      }
      #main>div>blockquote+table{
        margin-top:20px
      }
      #main>div>blockquote+blockquote{
        margin-top:16px
      }
      #main>div>table{
        margin:0;
        margin-top:12px;
        width:100%;
        border-spacing:0;
        border-collapse:separate;
        color:#2e2f30;
        font-size:14px
      }
      #main>div>table td,#main>div>table th{
        word-break:break-all;
        word-break:break-word;
        line-height:20px
      }
      #main>div>table td.c,#main>div>table th.c{
        text-align:center
      }
      #main>div>table td.l,#main>div>table th.l{
        text-align:left
      }
      #main>div>table td.r,#main>div>table th.r{
        text-align:right
      }
      #main>div>table thead tr{
        height:32px
      }
      #main>div>table thead tr th{
        padding:6px;
        background-color:#e8e8e8;
        border-left:1px solid #dedede
      }
      #main>div>table thead tr th:last-child{
        border-right:1px solid #dedede
      }
      #main>div>table thead tr:first-child th{
        border-top:1px solid #dedede
      }
      #main>div>table thead+tbody tr:nth-child(2n+1){
        background-color:#fff
      }
      #main>div>table thead+tbody tr:nth-child(2n){
        background-color:#f7f7f7
      }
      #main>div>table thead+tbody:nth-child(2n+1){
        background-color:#f7f7f7
      }
      #main>div>table thead+tbody:nth-child(2n){
        background-color:#fff
      }
      #main>div>table tbody tr{
        min-height:32px;
        background-color:#fff
      }
      #main>div>table tbody tr td,#main>div>table tbody tr th{
        padding:6px;
        border-top:1px solid #dedede;
        border-left:1px solid #dedede;
        vertical-align:middle
      }
      #main>div>table tbody tr td:last-child,#main>div>table tbody tr th:last-child{
        border-right:1px solid #dedede
      }
      #main>div>table tbody tr td>i,#main>div>table tbody tr th>i{
        filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=75);
        opacity:.75;
        font-size:13px
      }
      #main>div>table tbody tr td .p,#main>div>table tbody tr th .p{
        display:inline-block;
        position:relative;
        font-size:0;
        white-space:nowrap;
        width:20px;
        margin-right:2px;
        overflow:hidden;
        -moz-transition:width .3s;
        -o-transition:width .3s;
        -webkit-transition:width .3s;
        transition:width .3s;
        color:#646464
      }
      #main>div>table tbody tr td .p.s,#main>div>table tbody tr th .p.s{
        width:auto;
        font-size:14px
      }
      #main>div>table tbody tr td .p.s:after,#main>div>table tbody tr th .p.s:after{
        content:"";
        background-color:rgba(255,255,255,0)
      }
      #main>div>table tbody tr td .p:after,#main>div>table tbody tr th .p:after{
        content:".../";
        position:absolute;
        left:0;
        top:0;
        display:inline-block;
        width:100%;
        height:100%;
        font-size:14px;
        text-align:center;
        cursor:pointer;
        -moz-transition:background-color .3s;
        -o-transition:background-color .3s;
        -webkit-transition:background-color .3s;
        transition:background-color .3s
      }
      #main>div>table tbody tr th{
        background-color:#f7f7f7;
        text-align:right
      }
      #main>div>table tbody tr:last-child td,#main>div>table tbody tr:last-child th{
        border-bottom:1px solid #dedede
      }
      #main>div>div{
        text-align:right;
        color:#787878;
        font-size:12px;
        display:inline-block;
        width:100%;
        height:20px;
        line-height:20px
      }
      #main>div>div a{
        font-size:11px
      }
    </style>

    <script type="text/javascript">function init () { document.querySelectorAll("a.p").forEach(function() { this.onclick = function(e) { e.srcElement.classList.toggle("s") }; }); }</script>
  </head>

  <body lang="zh-tw" onload="init ();">
    <main id="main">
      <div>
        <h1 data-icon="⚠">錯誤</h1>

        <i></i>

        <blockquote><pre><?php echo $text;?></pre></blockquote>

  <?php if (!empty($contents['details'])) { ?>
          <h2>訊息</h2>
          <table>
            <tbody>
        <?php foreach ($contents['details'] as $detail) { ?>
                <tr>
                  <th width='100'><?php echo $detail['title'];?></th>
                  <td><?php echo $detail['content'];?></td>
                </tr>
        <?php } ?>
            </tbody>
          </table>
  <?php }
        if (!empty($contents['traces'])) { ?>
          <h2>回朔追蹤</h2>
          <table>
            <thead>
              <tr>
                <th width="50" class="c">順序</th>
                <th width="250">路徑</th>
                <th>資訊</th>
              </tr>
            </thead>
            <tbody>
        <?php $c = count($contents['traces']);
              foreach ($contents['traces'] as $i => $trace) {
                $dir = pathinfo($trace['path'], PATHINFO_DIRNAME);
                $base = pathinfo($trace['path'], PATHINFO_BASENAME); ?>
                <tr>
                  <td class="c"><?php echo $c - $i;?></td>
                  <td><?php echo ($dir && $dir != '.'  ? '<a class="p">' . $dir . DIRECTORY_SEPARATOR . '</a>' : '') . $base;?></td>
                  <td><?php echo $trace['info'];?></td>
                </tr>
        <?php } ?>
            </tbody>
          </table>
  <?php } ?>
        <i></i>
        <div>©2014 - <?php echo date ('Y');?> <a href="https://www.ioa.tw/" target="_blank">OA Wu</a>, All Rights Reserved.</div>
      </div>
    </main>
  </body>
</html>