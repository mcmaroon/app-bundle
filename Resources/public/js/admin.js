(function ($) {

    if (typeof APP.growl === 'function') {
        new APP.growl('.growl', {}).init();
    }

    if (typeof APP.sortable === 'function' && typeof APP.settings.paths === 'object' && typeof APP.settings.paths.sortable === 'string') {
        if ($('.tree-wrapper').length) {
            new APP.sortable('.tree-list', {
                url: APP.settings.paths.sortable
            }).init();
        }
    }

    if (typeof APP.map === 'function') {
        if ($('#map').length) {
            new APP.map('#map', {}).init();
        }
    }

    if (typeof APP.preview === 'function') {
        new APP.preview('.preview', {}).init();
    }
    
    if (typeof APP.previewSelect === 'function') {
        new APP.previewSelect('.preview-select', {}).init();
    }

    if (typeof APP.progress === 'function' && typeof APP.settings.paths === 'object' && typeof APP.settings.paths.convertprogress === 'string') {
        new APP.progress('.progress', {
            url: APP.settings.paths.convertprogress
        }).init();
    }
    
    if (typeof APP.uploadmultiple === 'function') {
        new APP.uploadmultiple('.uploadmultiple', {});
    }
    
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }

    /**
     * https://select2.github.io/
     */
    if (typeof $().chosen === 'function') {
        if ($('.select2').length) {
            $('.select2').chosen({
                width: '100%'
            });
        }
    }

    /**
     * http://mkoryak.github.io/floatThead/
     */
    if (typeof $().floatThead === 'function') {
        if ($('.table.table').length) {
            $('table.table').floatThead({
                zIndex: 1031
            });
        }
    }

    if (typeof $().datepicker === 'function') {
        if ($('.datepicker').length) {
            $(".datepicker").datepicker({
                dateFormat: 'yy-mm-dd'
            });
        }
    }

    /**
     * http://www.appelsiini.net/projects/lazyload
     */
    if (typeof $().lazyload === 'function') {
        if ($('img.lazyload').length) {
            $("img.lazyload").lazyload({
                event: "scroll"
            });
        }
    }

    /**
     * bulk actions
     */
    if ($('.form-bulk-all').length) {
        $('.form-bulk-all').click(function () {
            $(this).toggleClass('checked');
            $('table.table').not('.floatThead-table').find('.form-bulk-ids').prop('checked', $(this).hasClass('checked'));
        });
    }

    /**
     * add special class to body
     */
    if (typeof APP.settings === 'object' && typeof APP.settings.scrollingTop === 'number') {
        $(window).scroll(function () {
            if ($(window).scrollTop() > APP.settings.scrollingTop) {
                $('body').addClass('fixed');
            } else {
                $('body').removeClass('fixed');
            }

            if (($(window).scrollTop() + $(window).height()) >= $('html').height()) {
                $('body').addClass('page-end');
            } else {
                $('body').removeClass('page-end');
            }
        });
    }

})(jQuery);