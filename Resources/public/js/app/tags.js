(function ($) {

    if (typeof APP === 'object' && typeof $().chosen === 'function') {

        APP.tags = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            var defaults = {
                width: '100%',
                max_selected_options: 2
            };

            self.settings = $.extend({}, defaults, options);

            self.init = function () {
                if (self.selector.length) {
                    var coreOptions = {};
                    self.selector.chosen($.extend({}, coreOptions, self.settings));

                    self.selector.on('change', function (event, params) {
                        if (event.target == this) {
                            APP.trace($(this).val());
                        }
                    });

                    self.selector.on('chosen:maxselected', function (event, params) {
                        if (typeof APP.growl === 'function') {
                            new APP.growl('.growl').add('Maxselected', 'warning', 2000);
                        }
                    });
                }
            }
            ;
        }
        ;

    }

})(jQuery);