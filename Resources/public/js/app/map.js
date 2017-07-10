(function ($) {

    if (typeof APP === 'object' && typeof google === 'object') {

        APP.map = function (selector, options) {
            var self = this;

            self.selector = selector;
            self.map = null;
            self.markers = [];
            self.infoWindow = null;

            var defaults = {
                zoom: 6,
                minZoom: 6,
                maxZoom: 15,
                latitude: 52.2296756,
                longitude: 21.012228700000037,
                autocompleteStringPrefix: 'Poland PL',
                syncClass: 'map-sync'
            };

            self.settings = $.extend({}, defaults, options);

            self.init = function () {

                var mapOptions = {
                    zoom: self.settings.zoom,
                    minZoom: self.settings.minZoom,
                    maxZoom: self.settings.maxZoom,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    center: new google.maps.LatLng(self.settings.latitude, self.settings.longitude)
                };
                self.map = new google.maps.Map($(self.selector).get(0), mapOptions);
                self.infoWindow = new google.maps.InfoWindow;

                self.autocomplete();
                self.addMarker('', self.getLatitude(), self.getLongitude(), ['user']);
                self.addMarkersListenerByTags(['user'], {draggable: true});
                self.zoomByObjects('markers', ['user']);
                //self.showHideObjectsByTags('hide', 'markers', ['user']);

            };

            // ~

            self.setLatitude = function (coord) {
                $('body').find('input[data-coords="latitude"]').val(coord);
            };

            self.setLongitude = function (coord) {
                $('body').find('input[data-coords="longitude"]').val(coord);
            };

            self.getLatitude = function () {
                if (typeof $(self.selector).data('latitude') === 'undefined' || $(self.selector).data('latitude') === '') {
                    return self.settings.latitude;
                }
                return parseFloat($(self.selector).data('latitude'));
            };

            self.getLongitude = function () {
                if (typeof $(self.selector).data('longitude') === 'undefined' || $(self.selector).data('longitude') === '') {
                    return self.settings.longitude;
                }
                return parseFloat($(self.selector).data('longitude'));
            };

            // ~

            self.addMarker = function (title, latitude, longitude, tags, options) {
                var tags = (typeof tags === 'object' ? tags : []);
                tags.push('default');
                var marker = new google.maps.Marker({
                    title: (typeof title === 'string' ? title : ''),
                    tags: tags,
                    position: {
                        lat: parseFloat(latitude),
                        lng: parseFloat(longitude)
                    },
                    map: self.map,
                    options: (typeof options === 'object' ? options : {})
                });
                self.markers.push(marker);
            };

            // ~

            self.addMarkersListenerByTags = function (tags, options) {
                var elements = self.getObjectsByTags('markers', tags);
                var options = (typeof options === 'object' ? options : {});
                for (var id in elements) {
                    var element = elements[id];
                    for (var option in options) {
                        element.set(option, options[option]);
                    }
                    element.addListener('click', function () {
                        APP.trace('click:' + this.title);

                        // if first tag is numeric then sync by "syncClass"
                        if (typeof this.tags[0] === 'string' && !isNaN(parseFloat(this.tags[0])) && isFinite(this.tags[0])) {
                            var syncSelector = $('.' + self.settings.syncClass + '[data-id="' + parseInt(this.tags[0]) + '"]');
                            if (syncSelector.length) {
                                syncSelector.addClass(self.settings.syncClass + '-active');
                            }
                        }
                    });
                    google.maps.event.addListener(element, 'dragstart', function () {
                        $('body').find('input[data-coords="latitude"]').attr('disabled', 'disabled');
                        $('body').find('input[data-coords="longitude"]').attr('disabled', 'disabled');
                    });
                    google.maps.event.addListener(element, 'dragend', function () {
                        $('body').find('input[data-coords="latitude"]').removeAttr("disabled");
                        $('body').find('input[data-coords="longitude"]').removeAttr("disabled");
                        self.setLatitude(this.position.lat());
                        self.setLongitude(this.position.lng());
                    });
                }
            };

            // ~

            self.getObjectsByTags = function (objectTypeArrayName, tags) {
                var tags = (typeof tags === 'object') ? tags : [];
                var objects = self[objectTypeArrayName];
                var elements = [];
                if ((typeof objects === 'object') && (tags.length)) {
                    for (var id in objects) {
                        var marker = objects[id];
                        for (var tag in tags) {
                            if ((typeof tags[tag] === 'string') && (marker.tags.indexOf(tags[tag]) !== -1)) {
                                elements[id] = marker;
                            }
                        }
                    }
                }
                return elements;
            };

            // ~

            self.showHideObjectsByTags = function (action, objectTypeArrayName, tags) {
                var action = (action === 'show') ? self.map : null;
                var elements = self.getObjectsByTags(objectTypeArrayName, tags);
                for (var id in elements) {
                    var element = elements[id];
                    element.setMap(action);
                }

            };

            // ~

            self.searchAddress = function (address, callback) {

                var geocoderParams = {
                    address: (self.settings.autocompleteStringPrefix + ' ' + address).trim()
                };

                var geocoder = new google.maps.Geocoder();
                geocoder.geocode(geocoderParams, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        callback(results);
                    }
                });
            };

            // ~

            self.centerByAddress = function (address) {
                self.searchAddress(address, function (results) {
                    if (!results) {
                        return;
                    }

                    self.setLatitude(results[0].geometry.location.lat());
                    self.setLongitude(results[0].geometry.location.lng());
                    self.map.setCenter(results[0].geometry.location);

                    var userMarker = self.getObjectsByTags('markers', ['user'])[0];
                    if (typeof userMarker === 'object') {
                        userMarker.setPosition(results[0].geometry.location);
                        self.zoomByObjects('markers', ['user']);
                    }
                });
            };

            // ~

            self.zoomByMap = function () {
                var bounds = new google.maps.LatLngBounds();
                bounds.extend(self.map.center);
                self.map.fitBounds(bounds);
            };

            // ~

            self.zoomByObjects = function (objectTypeArrayName, tags) {
                var bounds = new google.maps.LatLngBounds();
                var elements = self.getObjectsByTags(objectTypeArrayName, tags);
                for (var id in elements) {
                    var element = elements[id];
                    bounds.extend(element.getPosition());
                }
                self.map.fitBounds(bounds);
            };

            // ~

            self.autocomplete = function () {
                $('#map-autocomplete').autocomplete({
                    delay: 300,
                    appendTo: "#map-autocomplete-wrapper",
                    source: function (request, response) {
                        self.searchAddress(request.term, function (results) {
                            if (!results) {
                                return;
                            }

                            var data = [];
                            $.each(results, function (index, result) {
                                data.push({
                                    label: result.formatted_address,
                                    location: result.geometry.location
                                });
                            });
                            response(data);
                        });
                    },
                    change: function (event, ui) {
                        self.centerByAddress($(this).val());
                    },
                    select: function (event, ui) {
                        if (ui.item) {
                            self.centerByAddress(ui.item.label);
                        }
                    }
                }).focus();
            };

        };

    }

})(jQuery);