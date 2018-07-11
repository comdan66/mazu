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



});