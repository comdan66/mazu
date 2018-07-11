/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

// need res/oaips-20180115.js
// need res/imgLiquid-min.js
// need res/timeago.js
window.gmc = function () { $(window).trigger ('gm'); };

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

  window.oaGmap = {
    keys: [],
    funcs: [],
    loaded: false,
    init: function () {
      if (window.oaGmap.loaded) return false;
      window.oaGmap.loaded = true;
      window.oaGmap.funcs.forEach (function (t) { t (); });
    },
    runFuncs: function () {
      if (!this.funcs.length) return true;

      $(window).bind ('gm', window.oaGmap.init);
      var k = this.keys[Math.floor ((Math.random() * this.keys.length))], s = document.createElement ('script');
      s.setAttribute ('type', 'text/javascript');
      s.setAttribute ('src', 'https://maps.googleapis.com/maps/api/js?' + (k ? 'key=' + k + '&' : '') + 'language=zh-TW&libraries=visualization&callback=gmc');
      (document.getElementsByTagName ('head')[0] || document.documentElement).appendChild (s);
      s.onload = window.oaGmap.init;
    },
    addFunc: function (func) {
      this.funcs.push (func);
    }
  };

  var color = {
    brightness: function (c) {
      return (c.r * 0.299 + c.g * 0.587 + c.b * 0.114) / 255 * 100;
    },
    has: function (c, d, h) {
      d = typeof d == 'undefined' ? {r:0, g:0, b:0} : d;
      h = typeof h == 'undefined' ? {r:255, g:255, b:255} : h;

      var a = this.brightness (c);
      var b = this.brightness (d);
      c = this.brightness (h);

      return Math.abs (a - c) > Math.abs (a - b);
    },
    text: function (c, d, h) {
      d = typeof d == 'undefined' ? {r:0, g:0, b:0} : d;
      h = typeof h == 'undefined' ? {r:255, g:255, b:255} : h;
      return this.has (c, d, h) ? h : d;
    },
    hex2rgb: function (str, toStr) {
      var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec (str);
      return toStr ? (result ? 'rgb(' + parseInt (result[1], 16) + ', ' + parseInt (result[2], 16) + ', ' +  parseInt (result[3], 16)+ ')' : 'rgb(255, 255, 255)') : (result ? {r: parseInt (result[1], 16), g: parseInt (result[2], 16), b: parseInt (result[3], 16)} : {r: 255, g: 255, b: 255});
    },
    rgbToHex: function (c) {
      return '#' + ((1 << 24) + (c.r << 16) + (c.g << 8) + c.b).toString (16).slice (1);
    },
    textColor: function (str) {
      return this.rgbToHex (this.text (this.hex2rgb (str)));
    }
  };

  $('.show-panel .date').each (function () {
    $(this).attr ('data-time', $.timeago ($(this).text ()));
  });
  $('.show-panel .color').each (function () {
    var bc = $(this).text ();
    
    var has = color.has (color.hex2rgb (bc));
    var rgb = color.hex2rgb (bc, true);


    $(this).attr ('data-rgb', rgb).addClass (has ? 'w' : 'b').css ({'background-color': bc});
  });

  $('.images').each (function () {
    var $img = $(this).find ('>img').clone (true);
    var $div = $img.map (function () {
      return $('<div />').append ($(this));
    });
    $(this).empty ().append ($div.toArray ());
    $div.imgLiquid ({ verticalAlign:'center' });
    window.oaips.set ($(this), '>div');
  });

  
  if ($('.map-show').length > 0) {
    oaGmap.addFunc (function () {
      $('.map-show').each (function () {
        var $that = $(this);
        var $gmap = $('<div />').addClass ('gmap').appendTo ($that);
        var $zoom = $('<div />').addClass ('zoom').append ($('<a />').text ('+')).append ($('<a />').text ('-')).appendTo ($that);
        var $full = $('<a />').addClass ('full').appendTo ($that);

        var lat = $(this).attr ('data-lat') !== undefined && !isNaN ($(this).attr ('data-lat')) && $(this).attr ('data-lat').length ? parseInt ($(this).attr ('data-lat'), 10) : 23.795397597978745;
        var lng = $(this).attr ('data-lng') !== undefined && !isNaN ($(this).attr ('data-lng')) && $(this).attr ('data-lng').length ? parseInt ($(this).attr ('data-lng'), 10) : 120.882568359375;
        var zoom = $(this).attr ('data-zoom') !== undefined && !isNaN ($(this).attr ('data-zoom')) && $(this).attr ('data-zoom').length ? parseInt ($(this).attr ('data-zoom'), 10) : 14;

        var position = new google.maps.LatLng (lat, lng);
        var gmap = new google.maps.Map ($gmap.get (0), { zoom: zoom, clickableIcons: false, disableDefaultUI: true, gestureHandling: 'greedy', center: position });

        gmap.mapTypes.set ('style1', new google.maps.StyledMapType ([{featureType: 'administrative.land_parcel', elementType: 'labels', stylers: [{visibility: 'on'}]}, {featureType: 'poi', elementType: 'labels.text', stylers: [{visibility: 'off'}]}, {featureType: 'poi.business', stylers: [{visibility: 'on'}]}, {featureType: 'poi.park', elementType: 'labels.text', stylers: [{visibility: 'on'}]}, {featureType: 'road.local', elementType: 'labels', stylers: [{visibility: 'on'}]}]));
        gmap.setMapTypeId ('style1');
        
        $zoom.find ('a').click (function () { gmap.setZoom (gmap.zoom + ($(this).index () ? -1 : 1)); });
        $full.click (function () { $that.toggleClass ('fixed'); $body.toggleClass ('mainMax'); google.maps.event.trigger (gmap, "resize"); });

        var marker = new google.maps.Marker ({
          map: gmap,
          zIndex: 2,
          draggable: false,
          position: position
        });
      });
    });
  }

  window.oaips.listenUrl ();
  window.oaGmap.runFuncs ();
});