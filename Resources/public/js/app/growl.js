(function ($) {

    if (typeof APP === 'object' && typeof $.growl === 'function') {

        APP.growl = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            var defaults = {
                growlTypesMap: {
                    success: 'notice',
                    warning: 'warning',
                    danger: 'error'
                },
                duration: 10000
            };

            self.settings = $.extend({}, defaults, options);

            /**
             * @param {string} message optional, default 'undefined'
             * @param {string} growlType optional, success or warning or danger default success
             * @param {integer} duration optional, default 10000 ms
             * @returns {growl}
             */
            self.add = function (message, growlType, duration) {

                var message = String(message);
                var growlType = (typeof growlType === 'string' && typeof self.settings.growlTypesMap[growlType] !== 'undefined') ? self.settings.growlTypesMap[growlType] : 'notice';
                var duration = (parseInt(duration) ? parseInt(duration) : self.settings.duration);

                if (typeof $.growl[growlType] === 'function') {
                    $.growl[growlType]({
                        title: '',
                        message: message,
                        size: "medium",
                        duration: duration,
                        location: "br"
                    });
                }

            };

            self.init = function () {
                if (self.selector.length) {
                    self.selector.parent('.alerts').addClass('sr-only');
                    $.each(self.selector, function (index, value) {
                        var growl = $(this);
                        var growlType = growl.data('type');
                        if (growlType !== 'undefined') {
                            self.add(growl.text(), growlType);
                        }
                    });
                }
            };

        };
    }

})(jQuery);