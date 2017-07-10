(function ($) {

    if (typeof APP === 'object' && typeof $().highlightText === 'function') {

        APP.highlighttext = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            var defaults = {
                text: null,
                minStringLength: 3
            };

            self.settings = $.extend({}, defaults, options);

            self.init = function () {
                if (self.selector.length && typeof self.settings.text === 'string') {
                    var arr = self.settings.text.trim().split(' ');
                    $.each(arr, function (index, value) {
                        if (value.trim().length >= self.settings.minStringLength) {
                            self.selector.highlightText(value.trim());
                        }
                    });
                }
            };

            self.init();

        };
    }

})(jQuery);