(function ($) {
    APP.screenResize = function (options) {

        var self = this;

        var defaults = {
            defultBreakpoint: 'xs',
            breakpoints: {
                xs: 0,
                sm: 576,
                md: 768,
                lg: 992,
                xl: 1200
            }
        };

        self.settings = $.extend({}, defaults, options);

        // ~

        self.getCurrentBreakpoint = function (width) {
            var breakpoint = self.settings.defultBreakpoint;
            $.each(self.settings.breakpoints, function (index, value) {
                if (value <= width) {
                    breakpoint = index;
                }
            });
            return breakpoint;
        };

        // ~

        self.init = function () {
            var obj = window;
            var running = false;
            var func = function () {
                if (running) {
                    return;
                }
                running = true;
                requestAnimationFrame(function () {
                    var event = new CustomEvent('screenResize');
                    event.width = obj.innerWidth;
                    event.height = obj.innerHeight;
                    event.breakpoint = self.getCurrentBreakpoint(event.width);
                    obj.dispatchEvent(event);
                    running = false;
                });
            };
            obj.addEventListener('resize', func);
        };

        self.init();

        window.dispatchEvent(new Event('resize'));
    };
})(jQuery);