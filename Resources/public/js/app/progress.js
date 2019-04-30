(function ($) {

    if (typeof APP === 'object') {

        APP.progress = function (selector, options) {
            var self = this;

            self.selector = selector;

            self.timer = null;

            self.xhr = null;

            var defaults = {
                waitingClassSelector: '.waiting',
                statusPrefixSelector: 'bg-',
                wrapperSelector: '.progress-wrapper',
                convertStartSelector: '.convert-start',
                convertEndSelector: '.convert-end',
                interval: 10000,
                url: null,
                percentStatuses: {
                    0: 'danger',
                    30: 'warning',
                    90: 'success'
                }
            };

            self.settings = $.extend({}, defaults, options);

            self.settings.url = $(self.selector).data('progress-url');

            self.getIds = function () {
                var ids = {};
                $.each($(self.selector).find(self.settings.waitingClassSelector), function (index, value) {
                    if (typeof $(this).data('id') === 'number') {
                        ids[$(this).data('id')] = $(this).data('id');
                    }
                });
                return ids;
            };

            self.setPercentStatus = function (item) {
                var $item = $(self.selector + ' ' + self.settings.waitingClassSelector + "[data-id='" + item.id + "']");
                if ($item.length) {
                    var progress = parseInt(item.progress);
                    var defaultStatusKey = Object.keys(self.settings.percentStatuses)[0];
                    var status = self.settings.percentStatuses[defaultStatusKey];
                    $.each(self.settings.percentStatuses, function (key, value) {
                        $item.children().removeClass(self.settings.statusPrefixSelector + value);
                        if (progress >= key) {
                            status = value;
                        }
                    });
                    $item.children().addClass(self.settings.statusPrefixSelector + status);
                }
            };

            self.sendRequest = function () {

                clearInterval(self.timer);

                var ids = self.getIds();

                if (self.xhr && self.xhr.readyState !== 4) {
                    self.xhr.abort();
                }

                if (!Object.keys(ids).length) {
                    return;
                }

                self.xhr = $.ajax({
                    cache: false,
                    url: self.settings.url,
                    method: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    complete: function () {
                    },
                    data: {
                        ids: ids
                    },
                    error: function (request, status, error) {
                        var prevInterval = self.settings.interval;
                        self.settings.interval = 60000;
                        self.loop();
                        self.settings.interval = prevInterval;
                        if (typeof APP.growl === 'function') {
                            new APP.growl('.growl').add('Progress error: ' + error, 'danger', 5000);
                        }
                    },
                    success: function (response, status, request) {
                        if (typeof response.items === 'object' && Object.keys(response.items).length) {
                            $.each(response.items, function (key, item) {

                                var $item = $(self.selector + ' ' + self.settings.waitingClassSelector + "[data-id='" + item.id + "']");

                                if ($item.length) {
                                    $item.children().css('width', parseInt(item.progress) + '%');

                                    self.setPercentStatus(item);

                                    if (parseInt(item.progress) >= 100) {
                                        $item.children().text(item.info);
                                        //$item.removeClass(self.settings.waitingClassSelector);

                                        if (typeof APP.growl === 'function') {
                                            //new APP.growl('.growl').add('Id' + item.id + ' - Name: ' + item.name + ' - converted', 'success', 5000);
                                        }
                                    }

                                    if (parseInt(item.progress) < 100) {
                                        $item.children().text(item.info);
                                    }

                                }
                            });
                        }

                        self.loop();
                    }
                });
            };

            self.loop = function () {
                self.timer = setInterval(self.sendRequest, self.settings.interval);
            };

            self.init = function () {
                if ($(self.selector).length && $(self.selector).find(self.settings.waitingClassSelector).length) {
                    self.loop();
                }
            };
        };

    }

})(jQuery);