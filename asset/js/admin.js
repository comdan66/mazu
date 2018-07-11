/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

window.gmc = function () { $(window).trigger ('gm'); };

function getStorage (key) { return (typeof Storage !== 'undefined') && (value = localStorage.getItem (key)) && (value = JSON.parse (value)) ? value : undefined; }
function setStorage (key, data) { try { if (typeof Storage === 'undefined') return false; localStorage.setItem (key, JSON.stringify (data)); return true; } catch (err) { console.error ('設定 storage 失敗！', error); return false; } }

window.storage = {};
window.storage.minMenu = {
  storageKey: 'oacms01.menu.min',
  isMin: function (val) { if (typeof val !== 'undefined') setStorage (this.storageKey, val); var tmp = getStorage (this.storageKey); return tmp ? tmp : false; },
};

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

  window.oaips.init ($body, window.callAddPv);


  if (typeof autosize !== 'undefined') {
    autosize ($('.autosize'));
  }
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
  
  $('form.search .conditions-btn').click (function () {
    $(this).parent ().toggleClass ('show');
  });

  $body.addClass (window.storage.minMenu.isMin () ? 'min' : null);
  $('table.list').each (function () {
    if ($(this).find ('tbody > tr').length) return;
    $(this).find ('tbody').append ($('<tr />').append ($('<td />').attr ('colspan', $(this).find ('thead > tr > th').length)));
  });

  setTimeout (function () { $body.addClass ('ani'); }, 500);
  
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

  if ($('.map-edit').length > 0) {
    oaGmap.addFunc (function () {
      $('.map-edit').each (function () {
        var $that = $(this);
        var $input = $that.find ('>input').clone (true);
        var $gmap = $('<div />').addClass ('gmap').appendTo ($that);
        var $zoom = $('<div />').addClass ('zoom').append ($('<a />').text ('+')).append ($('<a />').text ('-')).appendTo ($that);
        var $full = $('<a />').addClass ('full').appendTo ($that);

        var $lat = $('<input />').attr ('type', 'number').attr ('max', 85).attr ('min', -85).attr ('step', typeof $input.eq (0) === 'undefined' || $input.eq (0).attr ('step') === undefined ? 'any' : $input.eq (0).attr ('step')).attr ('name', typeof $input.eq (0) === 'undefined' || $input.eq (0).attr ('name') === undefined ? 'latitude' : $input.eq (0).attr ('name')).val (typeof $input.eq (0).val () === 'undefined' || isNaN ($input.eq (0).val ()) || !$input.eq (0).val ().length || $input.eq (0).val () > 85 || $input.eq (0).val () < -85 ? 23.795397597978745 : $input.eq (0).val ());
        var $lng = $('<input />').attr ('type', 'number').attr ('max', 180).attr ('min', -180).attr ('step', typeof $input.eq (1) === 'undefined' || $input.eq (1).attr ('step') === undefined ? 'any' : $input.eq (1).attr ('step')).attr ('name', typeof $input.eq (1) === 'undefined' || $input.eq (1).attr ('name') === undefined ? 'longitude' : $input.eq (1).attr ('name')).val (typeof $input.eq (1).val () === 'undefined' || isNaN ($input.eq (1).val ()) || !$input.eq (1).val ().length || $input.eq (1).val () > 180 || $input.eq (1).val () < -180 ? 120.882568359375 : $input.eq (1).val ());
        $input = $('<div />').addClass ('inputs').append ($lat).append ($lng).appendTo ($that);
        $that.find ('>input').remove ();

        var lat = parseFloat ($lat.val (), 10);
        var lng = parseFloat ($lng.val (), 10);
        var zoom = $(this).attr ('data-zoom') !== undefined && !isNaN ($(this).attr ('data-zoom')) ? parseInt ($(this).attr ('data-zoom'), 10) : 7;
        var position = new google.maps.LatLng (lat, lng);
        var gmap = new google.maps.Map ($gmap.get (0), { zoom: zoom, clickableIcons: false, disableDefaultUI: true, gestureHandling: 'greedy', center: position });

        gmap.mapTypes.set ('style1', new google.maps.StyledMapType ([{featureType: 'administrative.land_parcel', elementType: 'labels', stylers: [{visibility: 'on'}]}, {featureType: 'poi', elementType: 'labels.text', stylers: [{visibility: 'off'}]}, {featureType: 'poi.business', stylers: [{visibility: 'on'}]}, {featureType: 'poi.park', elementType: 'labels.text', stylers: [{visibility: 'on'}]}, {featureType: 'road.local', elementType: 'labels', stylers: [{visibility: 'on'}]}]));
        gmap.setMapTypeId ('style1');
        
        $zoom.find ('a').click (function () { gmap.setZoom (gmap.zoom + ($(this).index () ? -1 : 1)); });
        $full.click (function () { $that.toggleClass ('fixed'); $body.toggleClass ('mainMax'); google.maps.event.trigger (gmap, "resize"); });

        var marker = new google.maps.Marker ({
          map: gmap,
          zIndex: 2,
          draggable: true,
          position: position
        });

        gmap.addListener ('click', function (e) {
          $lat.val (e.latLng.lat ());
          $lng.val (e.latLng.lng ());

          marker.setOptions ({ position: e.latLng });
          // gmap.setOptions ({ center: e.latLng });
        });

        marker.addListener ('dragend', function (e) {
          $lat.val (e.latLng.lat ());
          $lng.val (e.latLng.lng ());
          // gmap.setOptions ({ center: e.latLng });
        });

        var keydown = function (e) {
          if (e.keyCode == 13)
            return false;
        };
        var keyup = function () {
          var lat = parseFloat ($lat.val (), 10);
          var lng = parseFloat ($lng.val (), 10);
          if (lat > 85 || lat < -85) {
            $lat.val (lat > 85 ? 85 : -85);
            return false;
          }
          if (lng > 180 || lng < -180) {
            $lng.val (lng > 180 ? 180 : -180);
            return false;
          }

          var position = new google.maps.LatLng (lat, lng);

          marker.setOptions ({ position:position });
          gmap.setOptions ({ center:position });
        };

        $lat.keydown (keydown).keyup (keyup);
        $lng.keydown (keydown).keyup (keyup);
      });
    });
  }

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

  window.oaGmap.runFuncs ();
  window.oaips.listenUrl ();
});