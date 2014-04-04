// Avoid `console` errors in browsers that lack a console.
(function() {
    var noop = function noop() {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = window.console || {};

    while (length--) {
        // Only stub undefined methods.
        console[methods[length]] = console[methods[length]] || noop;
    }
}());

//by Michalis Tzikas & Vasilis Lolos
//07-03-2012
//v1.0
(function( $ ){
  $.fn.linker = function(options) {
        var defaults = {
            target   : '', //blank,self,parent,top
            className : '',
            rel : ''
        };
        var options = $.extend(defaults, options);
        target_string = (options.target != '') ? 'target="_'+options.target+'"' : '';
        class_string = (options.className != '') ? 'class="'+options.className+'"' : '';
        rel_string = (options.rel != '') ? 'rel="'+options.rel+'"' : '';
        $(this).each(function(){
            t = $(this).text();
            t = t.replace(/(https\:\/\/|http:\/\/)([www\.]?)([^\s|<]+)/gi,'<a href="$1$2$3" '+target_string+' '+class_string+' '+rel_string+'>$1$2$3</a>');
            t = t.replace(/([^https\:\/\/]|[^http:\/\/]|^)(www)\.([^\s|<]+)/gi,'$1<a href="http://$2.$3" '+target_string+' '+class_string+' '+rel_string+'>$2.$3</a>');
            t = t.replace(/<([^a]|^\/a])([^<>]+)>/g, "&lt;$1$2&gt;").replace(/&lt;\/a&gt;/g, "</a>").replace(/<(.)>/g, "&lt;$1&gt;").replace(/\n/g, '<br />');    
            $(this).html(t);
        })
  };
})( jQuery );