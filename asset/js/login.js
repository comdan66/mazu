/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */
 
$(function () {
  $('.tabs').each(function() {
    var $panels = $(this).next();
    
    var $active = $(this).find('>a').click(function() {
      $(this).addClass('active').siblings().removeClass('active');
      $panels.attr('class', 'n' + $(this).index());
    }).filter('.active');

    $panels.attr('class', 'n' + $active.index());
  });
});