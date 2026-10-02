/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

import MapRenderer from 'handlers/map/renderer';

class GoogleMapsRenderer extends MapRenderer {

    /**
     * @param {module:handlers/map/renderer~addressData} addressData
     */
    render(addressData) {
        if ('google' in window && window.google.maps) {
            this.initMapGoogle(addressData);

            return;
        }

        // noinspection SpellCheckingInspection
        if (typeof window.mapapiloaded === 'function') {
            // noinspection SpellCheckingInspection
            const mapapiloaded = window.mapapiloaded;

            // noinspection SpellCheckingInspection
            window.mapapiloaded = () => {
                this.initMapGoogle(addressData);

                mapapiloaded();
            };

            return;
        }

        // noinspection SpellCheckingInspection
        window.mapapiloaded = () => this.initMapGoogle(addressData);

        let src = 'https://maps.googleapis.com/maps/api/js?callback=mapapiloaded&loading=async&v=weekly&libraries=marker';
        const apiKey = this.view.getConfig().get('googleMapsApiKey');

        if (apiKey) {
            src += '&key=' + apiKey;
        }

        const scriptElement = document.createElement('script');

        scriptElement.setAttribute('defer', 'defer');
        scriptElement.src = src;

        document.head.appendChild(scriptElement);
    }

    /**
     * @param {module:handlers/map/renderer~addressData} addressData
     */
    initMapGoogle(addressData) {
        // noinspection JSUnresolvedReference
        const geocoder = new google.maps.Geocoder();
        let map;

        const mapId = this.view.getConfig().get('googleMapsMapId') || 'DEMO_MAP_ID';

        try {
            // noinspection SpellCheckingInspection,JSUnresolvedReference
            map = new google.maps.Map(this.view.$el.find('.map').get(0), {
                zoom: 15,
                center: {lat: 0, lng: 0},
                scrollwheel: false,
                mapId: mapId,
            });
        }
        catch (e) {
            console.error(e.message);

            return;
        }

        let address = '';

        if (addressData.street) {
            address += addressData.street;
        }

        if (addressData.city) {
            if (address !== '') {
                address += ', ';
            }

            address += addressData.city;
        }

        if (addressData.state) {
            if (address !== '') {
                address += ', ';
            }

            address += addressData.state;
        }

        if (addressData.postalCode) {
            if (addressData.state || addressData.city) {
                address += ' ';
            }
            else {
                if (address) {
                    address += ', ';
                }
            }

            address += addressData.postalCode;
        }

        if (addressData.country) {
            if (address !== '') {
                address += ', ';
            }

            address += addressData.country;
        }

        // noinspection JSUnresolvedReference
        geocoder.geocode({'address': address}, (results, status) => {
            // noinspection JSUnresolvedReference
            if (status === google.maps.GeocoderStatus.OK) {
                // noinspection JSUnresolvedReference
                map.setCenter(results[0].geometry.location);

                // noinspection JSUnresolvedReference
                new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: results[0].geometry.location,
                });
            }
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default GoogleMapsRenderer;

