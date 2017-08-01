(function ($) {

    if (typeof APP === 'object') {

        APP.preview = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            self.xhr = null;

            var defaults = {
                entityNamespace: '',
                templatePart: ''
            };

            self.settings = $.extend({}, defaults, options);

            self.sendRequest = function (id) {

                if (self.xhr && self.xhr.readyState !== 4) {
                    self.xhr.abort();
                }
                var id = parseInt(id);
                var el = $(self.selector).filter('[data-id="' + id + '"]');
                var url = APP.settings.baseHost + APP.settings.baseUrl + '/admin/helpers/preview/' + self.settings.entityNamespace + '/' + id + (self.settings.templatePart ? '/' + self.settings.templatePart : '');
                if (el) {
                    el = el.parent();
                }

                self.xhr = $.ajax({
                    cache: false,
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        el.addClass('preloader');
                    },
                    complete: function () {
                        el.removeClass('preloader');
                    },
                    error: function (request, status, error) {
                        el.removeClass('preloader');
                    }
                });

                return self.xhr;
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

            self.handleData = function (response, status, request) {
                if (typeof $().modal === 'function') {
                    $('#prev-modal .modal-body').html(response.template);
                    $('#prev-modal').modal('show');
                }
            };

            // ~

            self.init = function () {
                if (self.selector.length) {
                    self.addModal();

                    self.selector.click(function () {
                        self.settings.entityNamespace = $(this).data('type');
                        var id = $(this).data('id');
                        self.sendRequest(id).done(self.handleData);
                    });

                }

            };
        };

    }

})(jQuery);