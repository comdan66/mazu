/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

// need res/oaips-20180115.js
// need res/jquery_ui_v1.12.0.js

$(function () {
  var $body = $('body');

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
  window.oaips.listenUrl ();
});