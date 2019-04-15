(function ($) {

    if (typeof APP.growl === 'function') {
        new APP.growl('.alerts .alert', {}).init();
    }

    if (typeof $().sparkline === 'function') {
        $('.sparklines').sparkline('html', {
            width: '9rem',
            height: '2rem',
            chartRangeMin: 0,
            spotRadius: 2,
            valueSpots:{'0:':'red'}
        });
    }

    if (typeof APP.stat === 'function') {
        new APP.stat('#stat', {
            url: '/admin/stat/async'
        });
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

    if (typeof APP.progress === 'function') {
        new APP.progress('[data-js="progress"]', {}).init();
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
                top: function ($table) {
                    var top = 0;
                    if ($('body > .navbar').length === 1) {
                        top = $('body > .navbar').innerHeight()
                    }
                    return top;
                }
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