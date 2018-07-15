/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

// need res/imgLiquid-min.js
// need res/timeago.js
// need res/jqui-datepick-20180116.js
// need res/oaips-20180115.js

function isJsonString (str) { try { return JSON.parse (str); } catch (e) { return null; } }
function getStorage (key) { return (typeof Storage !== 'undefined') && (value = localStorage.getItem (key)) && (value = JSON.parse (value)) ? value : undefined; }
function setStorage (key, data) { try { if (typeof Storage === 'undefined') return false; localStorage.setItem (key, JSON.stringify (data)); return true; } catch (err) { console.error ('設定 storage 失敗！', error); return false; } }

window.storage = {};
window.storage.minMenu = {
  storageKey: 'oacms01.menu.min',
  isMin: function (val) { if (typeof val !== 'undefined') setStorage (this.storageKey, val); var tmp = getStorage (this.storageKey); return tmp ? tmp : false; },
};

$(function () {
  var $body = $('body');
  
  window.loading = {
    $el: $('#loading'),
    ter: [],
    init: function () {
      this.$el = $('<div />').attr ('id', 'loading').addClass ('fbox');
      $body.append (this.$el).append ($('<div/>').addClass ('fbox-cover'));
    },
    clrTer: function (str) {
      this.ter.map (clearTimeout);
      this.ter = [];
    },
    show: function (str) {
      if (!this.$el.length)
        this.init ();

      if (typeof str !== 'undefined')
        this.$el.text (str);

      this.clrTer ();
      this.$el.addClass ('show');
      this.ter.push (setTimeout (function () {
        this.$el.addClass ('ani');
      }.bind (this), 100));
    },
    close: function (closure) {
      this.clrTer ();
      this.$el.removeClass ('ani');
      this.ter.push (setTimeout (function () {
        if (closure)
          closure ();

        this.$el.removeClass ('show');
      }.bind (this), 330));
    }
  };

  window.ajaxError = {
    $el: $('#ajax-error'),
    $el2: null,
    init: function () {
      this.$el2 = $('<div />');
      this.$el = $('<div />').attr ('id', 'ajax-error').addClass ('fbox').append (this.$el2).append ($('<a />').addClass ('icon-08').click (function () { this.close (); }.bind (this)));
      $body.append (this.$el).append ($('<div/>').addClass ('fbox-cover').click (function () { this.close (); }.bind (this)));
    },
    show: function (str) {
      if (!this.$el.length)
        this.init ();

      if (typeof str !== 'undefined')
        this.$el2.append ($('<b />').text ('請將下列訊息複製並告知給工程人員')).append ($('<div />').text (str));
      
      this.$el.addClass ('show');
    },
    close: function (closure) {
      this.$el.removeClass ('show');
      this.$el2.empty ();
    }
  };

  window.notification = {
    $el: $('#notification'),
    init: function () {
      this.$el = $('<div />').attr ('id', 'notification');
      $body.append (this.$el);
    },
    add: function (obj, closure, action) {
      if (!this.$el.length)
        this.init ();

      var $a = $('<a />').addClass ('icon-08').click (function (e) {
        e.stopPropagation ();

        if (closure)
          closure ();

        var $t = $(this).parent ().removeClass ('show');
        setTimeout (function () { $t.remove (); }, 300);
      });

      var $cover = null;

      if (typeof obj.icon !== 'undefined')
        $cover = $('<div />').addClass (obj.icon).addClass (typeof obj.color === 'undefined' ? 'font-icon' : null).css (typeof obj.color !== 'undefined' ? {color: obj.color} : {});

      if (typeof obj.img !== 'undefined')
        $cover = $('<div />').addClass ('_ic').append ($('<img />').attr ('src', obj.img));

      var $t = $('<div />').append ($cover)
                           .append ($('<span />').text (obj.title))
                           .append ($('<span />').html (obj.message))
                           .append ($a)
                           .addClass (action ? 'pointer' : null)
                           .click (action);

      this.$el.append ($t);
      setTimeout (function () { $t.addClass ('show'); }, 100);
      setTimeout (function () { $a.click (); }, 1000 * 10);
      return true;
    }
  };

  if (typeof $.fn.imgLiquid !== 'undefined') {
    $('._ic').imgLiquid ({ verticalAlign:'center' });
    $('._it').imgLiquid ({ verticalAlign:'top' });
  }

  if (typeof $.fn.imgLiquid !== 'timeago') {
    $('time[datetime]').timeago ();
  }
  $('a[data-method="delete"]').click (function () { return !confirm ('確定要刪除？') ? false : true; });

  $('#menu-main > div > div').each (function () {
    var $a = $(this).find ('>a');
    $(this).addClass ('n' + $a.length);
    $(this).prev ().click (function () { $(this).toggleClass ('active'); });
    if ($a.filter ('.active').length) $(this).prev ().addClass ('active');
  });

  $('#hamburger').click (function () {
    $body.toggleClass ('min');
    window.storage.minMenu.isMin ($body.hasClass ('min'));
  });

  if ($('input[type="date"]').prop ('type') != 'date' && typeof $.fn.datepicker !== 'undefined') {
    $('input[type="date"]').datepicker ({
      changeMonth: true,
      changeYear: true,
      firstDay: 0,
      dateFormat: 'yy-mm-dd',
      showOtherMonths: true,
      selectOtherMonths: true,
      zIndex:10,
    });
  }

  $body.addClass (window.storage.minMenu.isMin () ? 'min' : null);
  setTimeout (function () { $body.addClass ('ani'); }, 500);

  function reflashError () {
    window.notification.add ({
      icon: 'icon-38',
      color: 'rgba(234, 84, 75, 1.00)',
      title: '操作發生了錯誤！',
      message: '發生不明錯誤，為了確保資料正確性，請重新整理頁面然後回報給工程師。',
    }, function () {
      location.reload (true);
    });
  }

  function ajaxFail (result) {
    
  }

  function updateCounter (key, result) {
    if (typeof key === 'undefined')
      return;

    if (typeof this.$el === 'undefined')
      this.$el = $('*[data-cntlabel*="' + key + '"][data-cnt]');

    this.$el.each (function () { $(this).attr ('data-cnt', (result ? -1 : 1) + parseInt ($(this).attr ('data-cnt'), 10)); });
  }

  $('.switch.ajax[data-column][data-url][data-true][data-false]').each (function () {
    var $that = $(this),
        column = $that.data ('column'),
        url = $that.data ('url'),
        vtrue = $that.data ('true'),
        vfalse = $that.data ('false'),
        $inp = $that.find ('input[type="checkbox"]');

    $inp.click (function () {
      if ($that.hasClass ('loading')) return;

      var data = {};
      data[column] = $(this).prop ('checked') ? vtrue: vfalse;

      $that.addClass ('loading');

      $.ajax ({
        url: url,
        data: data,
        async: true, cache: false, dataType: 'json', type: 'POST'
      })
      .done (function (result) {
        if (typeof result[column] === 'undefined')
          return reflashError ();

        $(this).prop ('checked', result[column] == vtrue);
        $that.removeClass ('loading');

        if (result[column] == data[column])
          updateCounter ($that.data ('cntlabel'), result[column] == vtrue);
      }.bind ($(this)))
      .fail (function (result) {
        $(this).prop ('checked', !data[column]);
        $that.removeClass ('loading');

        window.notification.add ({icon: 'icon-38', color: 'rgba(234, 84, 75, 1.00)', title: '設定錯誤！', message: '※ 不明原因錯誤，請重新整理網頁確認。請點擊此訊息顯示詳細錯誤。'}, null, function () { window.ajaxError.show ((t = isJsonString (result.responseText)) !== null && t.message ? JSON.stringify (t) : result.responseText); });
      }.bind ($(this)));
    });
  });


  window.oaips = {
    ni: 0, $objs: {}, $pswp: null, $conter: null, callPvfunc : null,
    init: function ($b, c) { this.$pswp = $('<div class="pswp"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="關閉 (Esc)"></button><button class="pswp__button pswp__button--share" title="分享"></button><button class="pswp__button pswp__button--link" title="鏈結"></button><button class="pswp__button pswp__button--fs" title="全螢幕切換"></button><button class="pswp__button pswp__button--zoom" title="放大/縮小"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="上一張"></button><button class="pswp__button pswp__button--arrow--right" title="下一張"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>').appendTo ($b); this.$conter = this.$pswp.find ('div.pswp__caption__center'); if (c && typeof c === 'function') this.callPvfunc = c; return this; },
    show: function (index, $obj, da, fromURL) {
      if (isNaN (index) || !window.oaips.$pswp || !window.oaips.$conter) return;

      var items = $obj.get (0).$objs.map (function () {
        var $img = $(this).find ('img'), $figcaption = $(this).find ('figcaption'), $himg = $(this).find ('img.h');
        var $i = $himg.length ? $himg : $img;

        return {
          w: $i.get (0).width,
          h: $i.get (0).height,
          src: $i.attr ('src'),
          href: $(this).attr ('href'),
          title: $img.attr ('alt') && $img.attr ('alt').length ? $img.attr ('alt') : $figcaption.html (),
          content: $img.attr ('alt') && $img.attr ('alt').length ? $figcaption.html () : '',
          el: $(this).get (0)
        };
      }).toArray ();

      var options = {
        showHideOpacity: true,
        galleryUID: $obj.data ('pswp-uid'),
        showAnimationDuration: da ? 0 : 500,
        index: parseInt (index, 10) - (fromURL ? 1 : 0),
        getThumbBoundsFn: function (index) {
          var pageYScroll = window.pageYOffset || document.documentElement.scrollTop, rect = items[index].el.getBoundingClientRect ();
          return { x:rect.left, y:rect.top + pageYScroll, w:rect.width };
        }
      };

      var g = new PhotoSwipe (window.oaips.$pswp.get (0), PhotoSwipeUI_Default, items, options, $obj.get (0).$objs.map (function () {
        return $(this).data ('pvid') ? $(this).data ('pvid') : '';// $(this).data ('id');
      }));

      g.init (function (pvid) { if (!(window.oaips.callPvfunc && (typeof window.oaips.callPvfunc === 'function') && pvid.length &&( pvid.split ('-').length == 2))) return false; window.oaips.callPvfunc (pvid.split ('-')[0], pvid.split ('-')[1]) });

      window.oaips.$conter.width (Math.floor (g.currItem.w * g.currItem.fitRatio) - 20);
      g.listen ('beforeChange', function() { window.oaips.$conter.removeClass ('show'); window.oaips.$conter.width (Math.floor (g.currItem.w * g.currItem.fitRatio - 20)); });
      g.listen ('afterChange', function() { window.oaips.$conter.addClass ('show'); });
      g.listen ('resize', function() { window.oaips.$conter.width (Math.floor (g.currItem.w * g.currItem.fitRatio - 20)); });

      return this;
    },
    set: function (gs, fnx) {
      var $obj = (gs instanceof jQuery) ? gs : $(gs);
      if (!$obj.length) return false;

      $obj.each (function (i) {
        var $that = $(this);

        $that.data ('pswp-uid', window.oaips.ni + i + 1);
        $that.get (0).$objs = $that.find (fnx).each (function () { if ($(this).data ('ori')) $(this).append ($('<img />').attr ('src', $(this).data ('ori')).addClass ('h')); });
        $that.find (fnx).click (function () { window.oaips.show ($that.get (0).$objs.index ($(this)), $that); });

        window.oaips.$objs[window.oaips.ni + i] = $that;
      });

      window.oaips.ni = window.oaips.ni + 1;

      return this;
    },
    listenUrl: function () {
      var params = {};
      window.location.hash.replace ('#', '').split ('&').forEach (function (t, i) { if (!(t && (t = t.split ('=')).length && t[1].length)) return; params[t[0]] = t[1]; });
      if (!window.oaips.$objs[params.gid - 1] || Object.keys (params).length === 0 || typeof params.gid === 'undefined' || typeof params.pid === 'undefined') return false;
      setTimeout (function () { window.oaips.show (params.pid - 1, window.oaips.$objs[params.gid - 1], true, true); }, 500);
      return this;
    }
  };

  window.oaips.init ($body);

  $('form.search .conditions-btn').click (function () {
    $(this).parent ().toggleClass ('show');
  });
  
  $('.oaips').each (function () {
    var $oaips = $('<div />').addClass ('oaips');

    var $oaip = $(this).find ('img').map (function () {
      var $div = $('<div />').addClass ('oaip');
      if ($(this).attr ('data-pvid') !== undefined) $div.attr ('data-pvid', $(this).attr ('data-pvid'));
      if ($(this).attr ('data-ori') !== undefined) $div.attr ('data-ori', $(this).attr ('data-ori'));
      return $div.append ($('<img />').attr ('src', $(this).attr ('src')));
    });

    $oaips.append ($oaip.toArray ()).attr ('data-cnt', $oaip.length).appendTo ($(this));

    if (typeof $.fn.imgLiquid !== 'undefined') $oaip.imgLiquid ({ verticalAlign:'center' });
    window.oaips.set ($oaips, '.oaip');
  });

  if (typeof $.fn.sortable !== 'undefined') {
    $('table.list.dragable[data-sorturl]').each (function () {
      var $that = $(this);
      var ori = [];

      $that.sortable ({
        items: $that.find ('tr[data-sort][data-id]'),
        handle: $that.find ('span.drag'),
        connectWith: $that.find ('tbody'),
        placeholder: 'placeholder',
        start: function(e, ui){
          ui.placeholder.height (ui.item.height ());
          ori = $that.find ('tr[data-sort][data-id]:visible').map (function (i) {
            return {id: $(this).data ('id'), sort: $(this).data ('sort')};
          }).toArray ();
        },
        helper: function (e, $tr) {
          var $originals = $tr.children ();
          $tr.children ().each (function (index) { $(this).width ($originals.eq (index).outerWidth ()); });
          return $tr;
        },
        update: function (e, ui) {
          var now = $that.find ('tr[data-sort][data-id]:visible').map (function (i) {
            return {id: $(this).data ('id'), sort: $(this).data ('sort')};
          }).toArray ();

          if (ori.length != now.length)
            window.notification.add ({icon: 'icon-38', color: 'rgba(234, 84, 75, 1.00)', title: '設定錯誤！', message: '※ 不明原因錯誤，請重新整理網頁確認。請點擊此訊息顯示詳細錯誤。'}, null, function () {
              window.ajaxError.show (
                  'ori: ' + JSON.stringify (ori) +
                  'now: ' + JSON.stringify (now)
                );
            });

          var chg = [];
          for (var i = 0; i < ori.length; i++)
            if (ori[i].sort != now[i].sort)
              chg.push ({'id': now[i].id, 'ori': now[i].sort, 'now': ori[i].sort});
      
          $.ajax ({
            url: $that.data ('sorturl'),
            data: { changes: chg },
            async: true, cache: false, dataType: 'json', type: 'POST'
          })
          .done (function (result) {
            result.forEach (function (t) {
              $that.find ('tr[data-id="' + t.id + '"]').data ('sort', t.sort);
            });
          }.bind ($(this)))
          .fail (function (result) {
            window.notification.add ({icon: 'icon-38', color: 'rgba(234, 84, 75, 1.00)', title: '設定錯誤！', message: '※ 不明原因錯誤，請重新整理網頁確認。請點擊此訊息顯示詳細錯誤。'}, null, function () { window.ajaxError.show ((t = isJsonString (result.responseText)) !== null && t.message ? JSON.stringify (t) : result.responseText); });
          }.bind ($(this)));
        }
      });
    });
  }


  if (typeof autosize !== 'undefined') {
    autosize ($('.autosize'));
  }

  if (typeof $.fn.ckeditor !== 'undefined') {
    $('textarea.ckeditor').ckeditor ({
      filebrowserUploadUrl: '',
      filebrowserImageBrowseUrl: '',
      skin: 'oa',
      height: 300,
      resize_enabled: false,
      removePlugins: 'elementspath',
      toolbarGroups: [{ name: '1', groups: [ 'mode', 'tools', 'links', 'basicstyles', 'colors', 'insert', 'list', 'Table' ] }],
      removeButtons: 'Strike,Underline,Italic,HorizontalRule,Smiley,Subscript,Superscript,Forms,Save,NewPage,Print,Preview,Templates,Cut,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Form,RemoveFormat,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Flash,PageBreak,Iframe,About,Styles',
      extraPlugins: 'tableresize,dropler',
      droplerConfig: {
        backend: 'basic',
        settings: {
          uploadUrl: ''
        }
      },
      // contentsCss: 'assets/css/ckeditor_contents.css'
    });
  }

  if (typeof $.fn.OAdropUploadImg !== 'undefined') {
    $('.drop-img').OAdropUploadImg ();

    mutiImg = function ($obj) {
      if ($obj.length <= 0) return;

      $obj.on ('click', '.drop-img > a', function () {
        var $parent = $(this).parent ();
        $parent.remove ();
      });

      $obj.on ('change', '.drop-img > input[type="file"]', function () {
        if (!$(this).val ().length) return;

        var $parent = $(this).parent ();
        $parent.find ('input[type="hidden"]').remove ();

        if ($obj.find ('>.drop-img').last ().hasClass ('no')) return;
        var $n = $parent.clone ().removeAttr ('data-loading').addClass ('no');
        $n.find ('img').attr ('src', '');
        $n.find ('input').val ('');
        $n.OAdropUploadImg ().insertAfter ($parent);
      });
    };
    mutiImg ($('.multi-drop-imgs'));
  }

  $('.form').submit(function() {
    var $that = $(this);
    $(this).find('input[type="checkbox"][data-off]').each(function() {
      if ($(this).prop('checked') !== false) return ;
      $that.prepend($('<input />').attr('type', 'hidden').attr('name', $(this).attr('name')).val($(this).data('off')));
    });
    return true;
  });

  window.oaips.set ('.images', '.image');
  window.oaips.listenUrl ();
});