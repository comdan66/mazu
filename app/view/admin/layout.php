<!DOCTYPE html>
<html lang="tw">
  <head>
    <meta http-equiv="Content-Language" content="zh-tw" />
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui" />

    <title>後台系統</title>

    <?php echo $asset->renderCSS();?>
    <?php echo $asset->renderJS();?>

  </head>
  <body lang="zh-tw">

    <main id='main'>
      <header id='main-header'>
        <a id='hamburger' class='icon-01'></a>
        <nav><b><?php echo isset($title) && $title ? $title : '';?></b></nav>
        <a href='<?php echo Url::base('admin/logout');?>' class='icon-02'></a>
      </header>

      <div class='flash <?php echo $flash['type'];?>'><?php echo $flash['msg'];?></div>

      <div id='container'>
  <?php echo isset($content) ? $content : ''; ?>
      </div>

    </main>

    <div id='menu'>
      <header id='menu-header'>
        <a href='<?php echo Url::base();?>' class='icon-21'></a>
        <span>後台系統</span>
      </header>

      <div id='menu-user'>
        <figure class='_ic'>
          <img src="<?php echo Asset::url('asset/img/admin.png');?>">
        </figure>

        <div>
          <span>Hi, 您好!</span>
          <b><?php echo \M\Admin::current()->name;?></b>
        </div>
      </div>

      <div id='menu-main'>
        <div>
          <span class='icon-16'>文章設定</span>
          <div>
            <a href="<?php echo $url = Url::base('admin/tags');?>" class='icon-42<?php echo $url === $currentUrl ? ' active' : '';?>'>文章標籤</a>
            <a href="<?php echo $url = Url::base('admin/articles');?>" class='icon-22<?php echo $url === $currentUrl ? ' active' : '';?>'>文章列表</a>
          </div>
        </div>

      </div>
    </div>


    <footer id='footer'><span>後台版型設計 by </span><a href='https://www.ioa.tw/' target='_blank'>OAWU</a></footer>

  </body>
</html>
