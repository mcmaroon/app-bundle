(function ($) {

    if (typeof APP === 'object') {

        APP.theme = function (func) {
            var args = Array.prototype.slice.apply(arguments, [1]);

            return (APP.theme[func] || APP.theme.prototype[func]).apply(this, args);
        };

        APP.theme.prototype = {
            alert: function (type, message) {
                var output = '<div class="alert alert-' + type + ' growl" data-type="" role="alert">';
                output += '<span class="alert-icon fa fa-alert-' + type + '"></span>';
                output += '<span class="alert-caret fa fa-caret-right"></span>';
                output += message;
                output += '</div>';
                return output;
            }
        };
    }

})(jQuery);