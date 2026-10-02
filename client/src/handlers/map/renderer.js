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

/**
 * @module handlers/map/renderer
 */

/**
 * A map renderer.
 *
 * @abstract
 */
class MapRenderer {

    /**
     * @typedef {Object} module:handlers/map/renderer~addressData
     * @property {string|null} street
     * @property {string|null} city
     * @property {string|null} country
     * @property {string|null} state
     * @property {string|null} postalCode
     */

    /**
     * @param {import('views/fields/map').default} view A field view.
     */
    constructor(view) {
        this.view = view;
    }

    /**
     * @param {module:handlers/map/renderer~addressData} addressData
     * @abstract
     */
    render(addressData) {}
}

export default MapRenderer;
