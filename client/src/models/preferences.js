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

/** @module models/preferences */

import Model from 'model';

/**
 * User preferences.
 */
class Preferences extends Model {

    name = 'Preferences'
    entityType = 'Preferences'
    urlRoot = 'Preferences'

    /**
     * @private
     * @type {import('models/settings').default}
     */
    settings

    /**
     * Get dashlet options.
     *
     * @param {string} id A dashlet ID.
     * @returns {Object|null}
     */
    getDashletOptions(id) {
        const value = this.get('dashletsOptions') || {};

        return value[id] || null;
    }

    /**
     * Whether a user is portal.
     *
     * @returns {boolean}
     */
    isPortal() {
        return this.get('isPortalUser');
    }

    /**
     * @internal
     * @param {import('models/settings').default} settings
     */
    setSettings(settings) {
        this.settings = settings;
    }
}

export default Preferences;
