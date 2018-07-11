/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

window.gmc = function () { $(window).trigger ('gm'); };

function OAGM(t){this._div=null,this._option=Object.assign({className:"",top:0,left:0,width:32,height:32,html:"",map:null,position:null,css:{}},t),this._option.map&&this.setMap(this._option.map)}function initOAGM(){OAGM.prototype=new google.maps.OverlayView,Object.assign(OAGM.prototype,{setPoint:function(){if(!this._option.position)return this._div.style.left="-999px",void(this._div.style.top="-999px");var t=this.getProjection().fromLatLngToDivPixel(this._option.position);t&&(this._div.style.left=t.x-this._option.width/2+this._option.left+"px",this._div.style.top=t.y-this._option.height/2+this._option.top+"px")},draw:function(){if(!this._div){for(var t in this._div=document.createElement("div"),this._div.style.position="absolute",this._div.className=this._option.className,this._div.style.width=this._option.width+"px",this._div.style.height=this._option.height+"px",this._div.innerHTML=this._option.html,this._option.css)"width"!=t&&"height"!=t&&"top"!=t&&"left"!=t&&"bottom"!=t&&"right"!=t&&(this._div.style[t]=this._option.css[t]);var i=this;google.maps.event.addDomListener(this._div,"click",function(t){t.stopPropagation&&t.stopPropagation(),google.maps.event.trigger(i,"click")}),this.getPanes().overlayImage.appendChild(this._div)}this.setPoint()},remove:function(){return this._div&&(this._div.parentNode.removeChild(this._div),this._div=null),this},setWidth:function(t){return this._div&&(this._option.width=t,this._div.style.width=this._option.width+"px",this.setPoint()),this},setHeight:function(t){return this._div&&(this._option.height=t,this._div.style.height=this._option.height+"px",this.setPoint()),this},setTop:function(t){return this._div&&(this._option.top=t,this._div.style.top=this._option.top+"px",this.setPoint()),this},setLeft:function(t){return this._div&&(this._option.left=t,this._div.style.left=this._option.left+"px",this.setPoint()),this},setHtml:function(t){return this._div&&(this._option.html=t,this._div.innerHTML=this._option.html),this},setCss:function(t){if(!this._div)return this;for(var i in this._option.css=t,this._option.css)"width"!=i&&"height"!=i&&"top"!=i&&"left"!=i&&"bottom"!=i&&"right"!=i&&(this._div.style[i]=this._option.css[i]);return this},setClassName:function(t){return this._div&&(this._option.className=t,this._div.className=this._option.className),this},getClassName:function(){return this._option.className},setPosition:function(t){return this.map&&(this._option.position=t,this.setPoint()),this},getPosition:function(){return this._option.position}})}
var OAMC=function(t){this.uses=[],this.tmp=[],this.opts=Object.assign({map:null,unit:3,useLine:!1,middle:!0,latKey:"a",lngKey:"n",varKey:null,markersKey:null},t)};Object.assign(OAMC.prototype,{clean:function(){this.uses=[],this.tmp=[]},markers:function(n){if(!this.opts.map)return[];var a=this,t=this.opts.map.zoom,e=n.length-1,s=n.length-1;n.length;for(a.clean();0<=e;e--)if(!a.uses[e])for(a.tmp[e]={m:[n[e]],a:n[e][a.opts.latKey],n:n[e][a.opts.lngKey]},a.uses[e]=!0,s=e-1;0<=s;s--)if(!a.uses[s])if(30/Math.pow(2,t)/a.opts.unit<=Math.max(Math.abs(n[e][a.opts.latKey]-n[s][a.opts.latKey]),Math.abs(n[e][a.opts.lngKey]-n[s][a.opts.lngKey]))){if(a.opts.useLine)break}else a.uses[s]=!0,a.tmp[e].m.push(n[s]);var r=[];return a.tmp.forEach(function(t,e){var s=a.opts.middle?new google.maps.LatLng(t.m.map(function(t){return t[a.opts.latKey]}).reduce(function(t,e){return t+e})/t.m.length,t.m.map(function(t){return t[a.opts.lngKey]}).reduce(function(t,e){return t+e})/t.m.length):new google.maps.LatLng(t.a,t.n);null!==a.opts.markersKey&&(s[a.opts.markersKey]=t),null!==a.opts.varKey&&(s[a.opts.varKey]=n[e]),r.push(s)}),a.clean(),r}});

// jquery-OATA_v1.3.1
!function(t){"function"==typeof define&&define.amd?define(["jquery"],t):t(jQuery)}(function(t){function e(){var e=a(this),o=n.settings,s=t(this).data("tag");return isNaN(e.datetime)||(0==o.cutoff||r(e.datetime)<o.cutoff)&&(""==s||void 0===s?t(this).text(i(e.datetime)):t(this).data(s,i(e.datetime))),this}function a(e){if(e=t(e),!e.data("OATA")){e.data("OATA",{datetime:n.datetime(e)});var a=t.trim(e.text());n.settings.localeTitle?e.attr("title",e.data("OATA").datetime.toLocaleString()):!(a.length>0)||n.isTime(e)&&e.attr("title")||e.attr("title",a)}return e.data("OATA")}function i(t){return n.inWords(r(t))}function r(t){return(new Date).getTime()-t.getTime()}t.OATA=function(e){return i(e instanceof Date?e:"string"==typeof e?t.OATA.parse(e):"number"==typeof e?new Date(e):t.OATA.datetime(e))};var n=t.OATA;t.extend(t.OATA,{settings:{refreshMillis:6e4,allowFuture:!1,localeTitle:!1,cutoff:0,strings:{prefixAgo:null,prefixFromNow:null,suffixAgo:"ago",suffixFromNow:"from now",seconds:"less than a minute",minute:"about a minute",minutes:"%d minutes",hour:"about an hour",hours:"about %d hours",day:"a day",days:"%d days",month:"about a month",months:"%d months",year:"about a year",years:"%d years",wordSeparator:" ",numbers:[]}},inWords:function(e){function a(a,r){var n=t.isFunction(a)?a(r,e):a,o=i.numbers&&i.numbers[r]||r;return n.replace(/%d/i,o)}var i=this.settings.strings,r=i.prefixAgo,n=i.suffixAgo;this.settings.allowFuture&&0>e&&(r=i.prefixFromNow,n=i.suffixFromNow);var o=Math.abs(e)/1e3,s=o/60,u=s/60,m=u/24,d=m/365,l=45>o&&a(i.seconds,Math.round(o))||90>o&&a(i.minute,1)||45>s&&a(i.minutes,Math.round(s))||90>s&&a(i.hour,1)||24>u&&a(i.hours,Math.round(u))||42>u&&a(i.day,1)||30>m&&a(i.days,Math.round(m))||45>m&&a(i.month,1)||365>m&&a(i.months,Math.round(m/30))||1.5>d&&a(i.year,1)||a(i.years,Math.round(d)),f=i.wordSeparator||"";return void 0===i.wordSeparator&&(f=" "),t.trim([r,l,n].join(f))},parse:function(e){var a=t.trim(e);return a=a.replace(/\.\d+/,""),a=a.replace(/-/,"/").replace(/-/,"/"),a=a.replace(/T/," ").replace(/Z/," UTC"),a=a.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2"),a=a.replace(/([\+\-]\d\d)$/," $100"),new Date(a)},datetime:function(e){var a=n.isTime(e)?t(e).attr("datetime"):t(e).data("time");return n.parse(a)},isTime:function(e){return"time"===t(e).get(0).tagName.toLowerCase()}});var o={init:function(){var a=t.proxy(e,this);a();var i=n.settings;i.refreshMillis>0&&(this._OATAInterval=setInterval(a,i.refreshMillis))},update:function(a){var i=n.parse(a);t(this).data("OATA",{datetime:i}),n.settings.localeTitle&&t(this).attr("title",i.toLocaleString()),e.apply(this)},updateFromDOM:function(){t(this).data("OATA",{datetime:n.parse(n.isTime(this)?t(this).attr("datetime"):t(this).attr("title"))}),e.apply(this)},dispose:function(){this._OATAInterval&&(window.clearInterval(this._OATAInterval),this._OATAInterval=null)}};t.fn.OATA=function(t,e){var a=t?o[t]:o.init;if(!a)throw new Error("Unknown function name '"+t+"' for OATA");return this.each(function(){a.call(this,e)}),this},document.createElement("abbr"),document.createElement("time")});
jQuery.OATA.settings.strings={prefixAgo:null,prefixFromNow:"從現在開始",suffixAgo:"前",suffixFromNow:null,seconds:"不到 1 分鐘",minute:"約 1 分鐘",minutes:"%d 分鐘",hour:"約 1 小時",hours:"%d 小時",day:"約 1 天",days:"%d 天",month:"約 1 個月",months:"%d 個月",year:"約 1 年",years:"%d 年",numbers:[],wordSeparator:""};

$(function () {
  var $body = $('body');
  
  var _gmap = null;
  var _ms = [];
  var _ts = [];
  var _ter = null;

  var $_maps = null;
  var $_gmap = null;
  var $_zoom = null;
  var $_tip = null;


  window.oaGmap = {
    keys: ['AIzaSyDaIsR83jZSuQkmDGNgSvTVdy0ieWk6OwM'],
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

  function rePoint () {
    _ts = _ts.map (function (t) { t instanceof OAGM && t.setMap (null); t = null; return null; }).filter (function (t) { return t; });
    _ts = new OAMC ({latKey: 'lat', lngKey: 'lng', map: _gmap, unit: 1, useLine: false, middle: false, varKey: '_v', markersKey: '_c'}).markers (_ms).map (function (t) {
        var tmp = new OAGM({
          map: _gmap,
          position: t,
          width: 40,
          height: 40,
          className: 'user',
          html: "<div style=\"background-image: url('" + (t._v.pic.length ? t._v.pic : '/assets/img/admin.png') + "'); background-size: cover; background-position: center center; background-repeat: no-repeat;\"></div><span>" + (t._c.m.length > 1 ? t._c.m.length : '') + "</span>",
          css: {
            'border': '3px solid ' + (t._v.ios ? 'rgba(47, 74, 255, 1.00)' : 'rgba(75, 187, 138, 1.00)'),
          }
        });

        tmp.addListener('click', function () {
        });
        return tmp;
    });
  }
  function ajax (url, first) {

    $.ajax ({
      url: url,
      async: true, cache: false, dataType: 'json', type: 'GET'
    })
    .done (function (result) {
      _ms = result;

      rePoint();
    })
  }

  oaGmap.addFunc (function () {
    initOAGM ();

    $_maps       = $('#maps');
    $_gmap       = $('<div />').addClass ('gmap').appendTo ($_maps);
    $_zoom       = $('<div />').addClass ('zoom').append ($('<a />').text ('+')).append ($('<a />').text ('-')).appendTo ($_maps);
    $_tip        = $('<div />').addClass ('tip').append([$('<span>').text('iOS 系統'), $('<span>').text('安卓系統')]).appendTo ($_maps);

    var position = new google.maps.LatLng (23.79539759, 120.88256835);

    _gmap = new google.maps.Map ($_gmap.get (0), { zoom: 8, clickableIcons: false, disableDefaultUI: true, gestureHandling: 'greedy', center: position });
    _gmap.mapTypes.set ('style1', new google.maps.StyledMapType ([{featureType: 'administrative.land_parcel', elementType: 'labels', stylers: [{visibility: 'on'}]}, {featureType: 'poi', elementType: 'labels.text', stylers: [{visibility: 'off'}]}, {featureType: 'poi.business', stylers: [{visibility: 'on'}]}, {featureType: 'poi.park', elementType: 'labels.text', stylers: [{visibility: 'on'}]}, {featureType: 'road.local', elementType: 'labels', stylers: [{visibility: 'on'}]}]));
    _gmap.setMapTypeId ('style1');

    $_zoom.find ('a').click (function () { _gmap.setZoom (_gmap.zoom + ($(this).index () ? -1 : 1)); });

    ajax ($_maps.data ('url'), true);

    _gmap.addListener ('zoom_changed', function () {
      clearTimeout (_ter); _ter = setTimeout (rePoint, 350); });
  });

  window.oaGmap.runFuncs ();
});