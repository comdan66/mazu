var gulp       = require ('gulp'),
    livereload = require ('gulp-livereload'),
    uglifyJS   = require ('gulp-uglify'),
    htmlmin    = require ('gulp-html-minifier'),
    del        = require ('del'),
    chokidar   = require ('chokidar'),
    read       = require ('read-file'),
    writeFile  = require ('write'),
    gutil      = require ('gulp-util'),
    shell      = require ('gulp-shell'),
    colors     = gutil.colors;

gulp.task ('default', function () {
});

gulp.task ('watch', function () {
  console.log ('\n ' + colors.red ('•') + colors.cyan (' [開啟] ') + '設定相關 ' + colors.magenta ('watch') + ' 功能！');

  livereload.listen ({
    silent: true
  });

  var watcherReload = chokidar.watch (['./root/app/**/*.php', './root/assets/css/**/*.css', './root/assets/js/**/*.js'], {
    ignored: /(^|[\/\\])\../,
    persistent: true
  });

  watcherReload.on ('change', function (path) {
    console.log ('\n ' + colors.red ('•') + colors.yellow (' [重整] ') + '有檔案更新，檔案：' + colors.gray (path.replace (/\\/g,'/').replace (/.*\//, '')) + '');
    gulp.start ('reload');
    console.log ('    ' + colors.green ('reload') + ' 重新整理頁面成功！');
  }).on ('add', function (path) {
    console.log ('\n ' + colors.red ('•') + colors.yellow (' [重整] ') + '有新增檔案，檔案：' + colors.gray (path.replace (/\\/g,'/').replace (/.*\//, '')) + '');
    gulp.start ('reload');
    console.log ('    ' + colors.green ('reload') + ' 重新整理頁面成功！');
  }).on ('unlink', function (path) {
    console.log ('\n ' + colors.red ('•') + colors.yellow (' [重整] ') + '有檔案刪除，檔案：' + colors.gray (path.replace (/\\/g,'/').replace (/.*\//, '')) + '');
    gulp.start ('reload');
    console.log ('    ' + colors.green ('reload') + ' 重新整理頁面成功！');
  });

  var forders = ['admin', 'login', 'site'];
  forders.forEach (function (t) {
    var watcherStyle = chokidar.watch ('./root/assets/font/' + t + '/style.css', {
      ignored: /(^|[\/\\])\../,
      persistent: true
    })
    .on ('add', function (path) { update_icomoon_font_icon (t); })
    .on ('change', function (path) { update_icomoon_font_icon (t); });
    // .on ('unlink', function (path) { update_icomoon_font_icon (t); });
  });
});

// // ===================================================
function update_icomoon_font_icon (forder) {
  read ('./root/assets/font/' + forder + '/style.css', 'utf8', function (err, buffer) {
    var t = buffer.match (/\.icon-[a-zA-Z_\-0-9]*:before\s?\{\s*content:\s*"[\\A-Za-z0-9]*";(\s*color:\s*#[A-Za-z0-9]*;)?\s*}/g);
      if (!(t && t.length)) return;

      writeFile ('./root/assets/scss/icon-' + forder + '.scss', '@import "_oa";\n\n@include font-face("icomoon", font-files("' + forder + '/fonts/icomoon.eot", "' + forder + '/fonts/icomoon.woff", "' + forder + '/fonts/icomoon.ttf", "' + forder + '/fonts/icomoon.svg"));\n[class^="icon-"], [class*=" icon-"] {\n  font-family: "icomoon", Arial, "微軟正黑體", "Microsoft JhengHei", -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"; speak: none; font-style: normal; font-weight: normal; font-variant: normal;\n  @include font-smoothing(antialiased);\n}\n\n' + t.join ('\n'), function(err) {
        if (err) console.log ('\n ' + colors.red ('•') + colors.red (' [錯誤] ') + '寫入檔案失敗！');
        else console.log ('\n ' + colors.red ('•') + colors.yellow (' [icon] ') + '更新 icon 惹，目前有 ' + colors.magenta (t.length) + ' 個！');
      });
  });
}
// // ===================================================

gulp.task ('compass_compile', shell.task ('compass compile'));

// // ===================================================

gulp.task ('reload', function () {
  livereload.changed ();
});

// // ===================================================

gulp.task ('minify', function () {
  gulp.start ('js-uglify');
  gulp.start ('minify-html');
});
gulp.task ('js-uglify', function () {
  gulp.src ('./root/assets/js/**/*.js')
      .pipe (uglifyJS ())
      .pipe (gulp.dest ('./root/assets/js/'));
});
gulp.task ('minify-html', function () {
  gulp.src ('./root/app/**/*.php')
      .pipe (htmlmin ({collapseWhitespace: true}))
      .pipe (gulp.dest ('./root/assets/'));
});

// // ===================================================

gulp.task ('gh-pages', function () {
  del (['./root/assets']);
});