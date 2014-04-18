/* Utilities
*/

// JQuery plugin for getting argument parameters
// http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
(function($) {
  $.QueryString = (function(a) {
    var b = {};
    if(!a) return b;
    for(var i = 0; i < a.length; ++i) {
      var p = a[i].split('=');
      if(p.length != 2) continue;
      b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, ' '));
    }
    return b;
  })(window.location.search.substr(1).split('&'))
})(jQuery);