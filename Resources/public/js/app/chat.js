(function ($) {

    if ((typeof APP === 'object') && (typeof io) === 'function') {

        APP.chat = function (selector, options) {
            var self = this;

            self.selector = selector;

            self.websocket = null;

            var defaults = {
                url: null
            };

            self.settings = $.extend({}, defaults, options);

            self.sendMessage = function () {
                var msg = {
                    type: "message",
                    text: $(self.selector + '-input').val(),
                    id: 1,
                    date: Date.now()
                };
                self.websocket.send(JSON.stringify(msg));
                $(self.selector + '-input').val('');
            };

            self.init = function () {
                if (self.selector.length && self.settings.url) {
                    self.websocket = new WebSocket(self.settings.url);

                    self.websocket.onopen = function (e) {
                        //APP.trace('onopen');
                        $(self.selector + '-input').keypress(function (e) {
                            if (e.which === 13) {
                                self.sendMessage();
                            }
                        });

                        $(self.selector + '-btn').click(function () {
                            self.sendMessage();
                        });
                    };

                    self.websocket.onclose = function (e) {
                        //APP.trace('onclose');
                    };

                    self.websocket.onerror = function (e) {
                        //APP.trace('onerror');
                    };

                    self.websocket.onmessage = function (e) {
                        var data = JSON.parse(e.data);
                        $(self.selector + '-output').append("<div>" + data.text + "</div>");
                    };
                }
            };

        };

    }

})(jQuery);