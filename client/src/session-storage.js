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

/** @module session-storage */

/**
 * A session storage. Cleared when a page session ends.
 */
class SessionStorage {

    /** @private */
    storageObject = sessionStorage

    /**
     * Get a value.
     *
     * @param {string} name A name.
     * @returns {*} Null if not set.
     */
    get(name) {
        let stored;

        try {
            stored = this.storageObject.getItem(name);
        }
        catch (error) {
            console.error(error);

            return null;
        }

        if (stored) {
            let result = stored;

            if (stored.length > 9 && stored.substring(0, 9) === '__JSON__:') {
                const jsonString = stored.slice(9);

                try {
                    result = JSON.parse(jsonString);
                }
                catch (error) {
                    result = stored;
                }
            }

            return result;
        }

        return null;
    }

    /**
     * Set (store) a value.
     *
     * @param {string} name A name.
     * @param {*} value A value.
     */
    set(name, value) {
        if (value === null) {
            this.clear(name);

            return;
        }

        if (
            value instanceof Object ||
            Array.isArray(value) ||
            value === true ||
            value === false ||
            typeof value === 'number'
        ) {
            value = '__JSON__:' + JSON.stringify(value);
        }

        try {
            this.storageObject.setItem(name, value);
        }
        catch (error) {
            console.error(error);
        }
    }

    /**
     * Has a value.
     *
     * @param {string} name A name.
     * @returns {boolean}
     */
    has(name) {
        return this.storageObject.getItem(name) !== null;
    }

    /**
     * Clear a value.
     *
     * @param {string} name A name.
     */
    clear(name) {
        for (const i in this.storageObject) {
            if (i === name) {
                delete this.storageObject[i];
            }
        }
    }
}

export default SessionStorage;
