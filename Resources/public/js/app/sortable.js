(function ($) {

    if (typeof APP === 'object' && typeof $().sortable === 'function') {

        APP.sortable = function (selector, options) {
            var self = this;

            self.selector = $(selector);

            self.sortableItem = null;

            self.sortableItemLevelRoot = null;

            self.sortableRootItems = {};

            self.sortableRootItemsPrevIds = {};

            self.sortableRootItemsIds = {};

            self.xhr = null;

            var defaults = {
                wrapper: '.tree-wrapper',
                url: null
            };

            self.settings = $.extend({}, defaults, options);

            self.setSortableItem = function (uiItem) {
                self.sortableItem = uiItem;
            };

            self.getSortableItem = function () {
                return self.sortableItem;
            };

            self.getSortableItemId = function () {
                return self.sortableItem.data('id');
            };

            self.getSortableItemName = function () {
                return self.sortableItem.find('.form-control').val().trim();
            };

            self.sortableItemToogleSave = function () {
                self.getSortableItem().children('.tree-leaf-title').toggleClass('bg-danger');
            };

            self.setSortableItemLevelRoot = function () {
                var item = self.getSortableItem();
                if (item.parent('ul').length) {
                    self.sortableItemLevelRoot = item.parent('ul');
                }
            };

            self.getSortableItemLevelRoot = function () {
                return self.sortableItemLevelRoot;
            };

            self.getSortableItemLevelRootName = function () {
                var item = self.getSortableItemLevelRoot().parent().children('.tree-leaf-title').find('.form-control');
                if (typeof item === 'object' && typeof item[0] !== 'undefined') {
                    return item.val().trim();
                }
                return 'Root';
            };

            self.setSortableRootItems = function () {
                var item = self.getSortableItemLevelRoot();
                if (typeof item === 'object') {
                    self.sortableRootItems = item.children('li').not('.ui-sortable-placeholder');
                }
            };

            self.getSortableRootItems = function () {
                return self.sortableRootItems;
            };

            self.getSortableRootItemsIds = function () {
                $.each(self.getSortableRootItems(), function (key, value) {
                    if (typeof $(value).data('id') === 'number') {
                        self.sortableRootItemsIds[$(value).data('id')] = parseInt(key);
                    }
                });

                if ((Object.keys(self.sortableRootItemsPrevIds).length === 0)) {
                    self.sortableRootItemsPrevIds = self.sortableRootItemsIds;
                }

                return self.sortableRootItemsIds;
            };

            self.isChanged = function () {
                var diff = true;
                var sum1 = '';
                var sum2 = '';
                $.each(self.sortableRootItemsPrevIds, function (key1, value1) {
                    sum1 += key1 + value1;
                });
                $.each(self.getSortableRootItemsIds(), function (key2, value2) {
                    sum2 += key2 + value2;
                });
                if (sum1 === sum2) {
                    return false;
                }
                return diff;
            };

            self.clearIds = function () {
                self.sortableRootItemsPrevIds = {};
                self.sortableRootItemsIds = {};
            };

            self.sendSortRequest = function () {

                if (self.xhr && self.xhr.readyState !== 4) {
                    self.xhr.abort();
                }

                self.xhr = $.ajax({
                    cache: false,
                    url: self.settings.url,
                    method: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        self.selector.parent().addClass('preloader');
                        self.sortableItemToogleSave();
                    },
                    complete: function () {
                        self.selector.parent().removeClass('preloader');
                        self.sortableItemToogleSave();
                    },
                    data: {
                        ids: self.getSortableRootItemsIds()
                    },
                    error: function (request, status, error) {

                    },
                    success: function (response, status, request) {
                        if (response.status) {
                            if (typeof APP.growl === 'function') {
                                new APP.growl('.growl').add('Update orders success', 'success', 2000);
                            }
                        }
                    }
                });
            };

            self.init = function () {
                if ($(self.settings.wrapper).length) {                    

                    if (self.selector.length) {
                        self.selector.sortable({
                            cursor: "move",
                            forcePlaceholderSize: true,
                            placeholder: 'ui-state-highlight',
                            refreshPositions: true,
                            iframeFix: true,
                            start: function (event, ui) {
                                self.setSortableItem(ui.item);
                                self.setSortableItemLevelRoot();
                                self.setSortableRootItems();
                                self.getSortableRootItemsIds();
                            },
                            stop: function (event, ui) {
                                self.setSortableRootItems();
                                var changed = self.isChanged();

                                if (changed === false) {
                                    if (typeof APP.growl === 'function') {
                                        //new APP.growl('.growl').add('No changes', 'warning', 2000);
                                    }
                                }

                                if (changed === true) {
                                    self.sendSortRequest();
                                }

                                self.clearIds();
                            }
                        });
                    }
                }
            };
        };

    }

})(jQuery);