(function ($) {

    if ((typeof APP === 'object') && (typeof $().autocomplete) === 'function') {

        APP.autocomplete = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            self.xhr = null;

            var defaults = {
                autoFocus: true,
                delay: 300,
                minLength: 2,
                appendTo: null,
                autocompleteUrl: null,
                searchUrl: null
            };

            self.settings = $.extend({}, defaults, options);

            $.widget("custom.searchcomplete", $.ui.autocomplete, {
                _create: function () {
                    this._super();
                    this.widget().menu("option", "items", "> :not(.ui-autocomplete-group)");
                },
                _resizeMenu: function () {
                    var ul = this.menu.element;
                    ul.outerWidth('100%');
                },
                _renderMenu: function (ul, items) {
                    var that = this,
                            currentGroup = "";
                    $.each(items, function (index, item) {
                        var li;
                        if (item.group != currentGroup) {
                            ul.append("<li class='ui-autocomplete-group'>" + item.group + "</li>");
                            currentGroup = item.group;
                        }
                        li = that._renderItemData(ul, item);
                        if (item.group) {
                            li.attr("aria-label", item.group + " : " + item.label);
                        }
                    });
                }
            });

            self.init = function () {
                if (self.selector.length) {
                    self.selector.searchcomplete({
                        autoFocus: self.settings.autoFocus,
                        delay: self.settings.delay,
                        minLength: self.settings.minLength,
                        appendTo: self.settings.appendTo,
                        source: function (request, response) {

                            if (self.xhr && self.xhr.readyState !== 4) {
                                self.xhr.abort();
                            }

                            self.xhr = $.ajax({
                                url: self.settings.autocompleteUrl,
                                dataType: "json",
                                data: {
                                    text: request.term
                                },
                                success: function (data) {
                                    if (typeof data.items === 'object') {
                                        response(data.items);
                                    }
                                }
                            });
                        },
                        select: function (event, ui) {
                            if (ui.item) {
                                //if (ui.item.type === 'product') {
                                var searchUrl = self.settings.searchUrl + '?text=' + ui.item.label;
                                //}

                                if (typeof searchUrl === 'string') {
                                    location.replace(searchUrl);
                                }
                            }
                        },
                        close: function (event, ui) {
                            //console.log('close');
                        }
                    });
                }
            };

        };

    }

})(jQuery);