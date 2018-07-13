/*
* @Author: SisSoftwares WEB (Sistemas PHP)
* @Date:   2018-06-07 13:12:35
* @Last Modified by:   Usuario
* @Last Modified time: 2018-07-12 22:44:09
*/

/*menu handler*/
$(function(){

  function getPart(str, separator, part) {
    return str.split(separator)[part];
  }

  var url = window.location +''; // Add "+''" to get location like string
  var urlClass = getPart(url, '?', 1);
  urlClass = urlClass.replace('#',''); // remove '#' if present in URL
  $('li a[href="'+ 'index.php?' + urlClass +'"]').closest('ul').closest('li').toggleClass('active');
  $('li a[href="'+ 'index.php?' + urlClass +'"]').closest('ul').addClass('menu-open');

  // Toggle check-icon
  var url = window.location+'';
  // $('ul.sidebar-menu a').filter(function() {
  $('ul.ml-menu.level-2 a').filter(function() {
     return this.href == url;
  }).parent().find('i').toggleClass('fa-circle-o fa-check-circle');


});
