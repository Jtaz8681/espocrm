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

/** @module models/settings */

import Model from 'model';

/**
 * A config.
 */
class Settings extends Model {

    name = 'Settings'
    entityType = 'Settings'
    urlRoot = 'Settings'

    /**
     * Load.
     *
     * @returns {Promise}
     */
    load() {
        return new Promise(resolve => {
            this.fetch()
                .then(() => resolve());
        });
    }

    /**
     * Get a value by a path.
     *
     * @param {string[]} path A path.
     * @returns {*} Null if not set.
     */
    getByPath(path) {
        if (!path.length) {
            return null;
        }

        let p;

        for (let i = 0; i < path.length; i++) {
            const item = path[i];

            if (i === 0) {
                p = this.get(item);
            }
            else {
                if (item in p) {
                    p = p[item];
                }
                else {
                    return null;
                }
            }

            if (i === path.length - 1) {
                return p;
            }

            if (p === null || typeof p !== 'object') {
                return null;
            }
        }
    }
}

export default Settings;
