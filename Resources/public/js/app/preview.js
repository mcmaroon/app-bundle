(function ($) {

    if (typeof APP === 'object' && typeof $().modal === 'function') {

        APP.preview = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            self.xhr = null;

            var defaults = {};

            self.settings = $.extend({}, defaults, options);


            self.sendRequest = function (el) {

                var type = el.data('type');
                var id = el.data('id');
                if (typeof type !== 'string' && typeof id !== 'number') {
                    self.xhr.abort();
                }

                if (self.xhr && self.xhr.readyState !== 4) {
                    self.xhr.abort();
                }

                self.xhr = $.ajax({
                    cache: false,
                    url: APP.settings.baseHost + APP.settings.baseUrl + '/admin/helpers/preview/' + type + '/' + id,
                    method: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        el.parent().addClass('preloader');
                    },
                    complete: function () {
                        el.parent().removeClass('preloader');
                    },
                    error: function (request, status, error) {
                        el.parent().removeClass('preloader');
                    },
                    success: function (response, status, request) {
                        $('#prev-modal .modal-body').html(response.template);
                        $('#prev-modal').modal('show');
                    }
                });
            };

            // ~

            self.addModal = function () {
                var output = '<div class="modal fade" id="prev-modal">';
                output += '<div class="modal-dialog modal-lg">';
                output += '<div class="modal-content">';
                output += '<div class="modal-header">';
                output += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                output += '<h4 class="modal-title">Preview</h4>';
                output += '</div>';
                output += '<div class="modal-body"></div>';
                output += '</div>';
                output += '</div>';
                output += '</div>';
                $('body').append(output);
            };

            // ~

            self.init = function () {
                if (self.selector.length) {
                    self.addModal();

                    self.selector.click(function () {
                        self.sendRequest($(this));
                    });

                }

            };

            self.init();
        };

    }

})(jQuery);