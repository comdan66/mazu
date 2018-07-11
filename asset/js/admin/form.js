/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

// need res/autosize-3.0.8.js
// need res/OAdropUploadImg-20180115.js
// need res/ckeditor_d2015_05_18/ckeditor.js
// need res/ckeditor_d2015_05_18/adapters/jquery.js
// need res/ckeditor_d2015_05_18/plugins/tabletools/tableresize.js
// need res/ckeditor_d2015_05_18/plugins/dropler/dropler.js'

window.gmc = function () { $(window).trigger ('gm'); };

$(function () {
  var $body = $('body');

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

  $('.multi-datas').each (function () {
    var $that = $(this);
    $that.find ('.datas:not(.demo) .del').click (function () { $(this).parents ('.datas').remove (); });

    var $demo = $that.find ('.demo');
    
    if (!$demo.length)
      return;
    $demo.find ('[required]').each (function () {
      $(this).attr ('data-required', true).removeAttr ('required');
    });

    var $btns = $that.find ('.btns');
    
    $btns.find ('.add').click (function () {
      var index = parseInt ($that.attr ('data-index'), 10);
      var $new = $demo.clone ().removeClass ('demo');
      
      $new.find ('.del').click (function () { $(this).parents ('.datas').remove (); });
      $new.find ('[data-prefix][data-name]').each (function () { $(this).attr ('name', $(this).data ('prefix') + '[' + index + ']' + $(this).data ('name')).removeAttr ('data-prefix').removeAttr ('data-name'); });
      $new.find ('[data-required]').each (function () { if ($(this).attr ('data-required')) $(this).prop ('required',  true).removeAttr ('data-required'); });
      $new.insertBefore ($(this));

      $that.attr ('data-index', index + 1);
    }.bind ($btns));
  });

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

        var $lat = $('<input />').attr ('type', 'number').attr ('max', 85).attr ('min', -85).attr ('step', typeof $input.eq (0) === 'undefined' || $input.eq (0).attr ('step') === undefined ? 'any' : $input.eq (0).attr ('step')).attr ('name', typeof $input.eq (0) === 'undefined' || $input.eq (0).attr ('name') === undefined ? 'latitude' : $input.eq (0).attr ('name')).val (typeof $input.eq (0).val () === 'undefined' || isNaN ($input.eq (0).val ()) || !$input.eq (0).val ().length || $input.eq (0).val () > 85 || $input.eq (0).val () < -85 ? 23.79539759 : $input.eq (0).val ());
        var $lng = $('<input />').attr ('type', 'number').attr ('max', 180).attr ('min', -180).attr ('step', typeof $input.eq (1) === 'undefined' || $input.eq (1).attr ('step') === undefined ? 'any' : $input.eq (1).attr ('step')).attr ('name', typeof $input.eq (1) === 'undefined' || $input.eq (1).attr ('name') === undefined ? 'longitude' : $input.eq (1).attr ('name')).val (typeof $input.eq (1).val () === 'undefined' || isNaN ($input.eq (1).val ()) || !$input.eq (1).val ().length || $input.eq (1).val () > 180 || $input.eq (1).val () < -180 ? 120.88256835 : $input.eq (1).val ());
        $input = $('<div />').addClass ('inputs').append ($lat).append ($lng).appendTo ($that);
        $that.find ('>input').remove ();

        var lat = parseFloat ($lat.val (), 10);
        var lng = parseFloat ($lng.val (), 10);
        var zoom = $(this).attr ('data-zoom') !== undefined && !isNaN ($(this).attr ('data-zoom')) && $(this).attr ('data-zoom').length ? parseInt ($(this).attr ('data-zoom'), 10) : 7;
        
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

  window.oaGmap.runFuncs ();
});