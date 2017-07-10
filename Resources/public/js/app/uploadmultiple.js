(function ($) {

    if (typeof APP === 'object') {

        APP.uploadmultiple = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            var defaults = {};

            self.settings = $.extend({}, defaults, options);

            self.addTagForm = function () {
                var prototype = self.selector.data('prototype');
                var index = self.selector.data('index');
                var newForm = prototype.replace(/__name__/g, index);
                newForm = newForm.replace(/__label__/g, '');
                self.selector.data('index', index + 1);
                self.selector.append(newForm);
            };

            self.addButton = function () {
                var button = $('<a href="#" class="btn-uploadmultiple btn btn-default btn-block">Add</a>');
                self.selector.prepend(button);
            };

            self.init = function () {
                if (self.selector.length) {
                    self.addButton();

                    self.selector.data('index', self.selector.find(':input').length);

                    $('.btn-uploadmultiple').on('click', function (e) {
                        e.preventDefault();
                        self.addTagForm();
                    });
                }
            };

            self.init();
        };

    }

})(jQuery);