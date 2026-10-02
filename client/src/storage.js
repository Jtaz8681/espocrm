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

/** @module storage */

/**
 * A storage. Data is saved across browser sessions, has no expiration time.
 */
class Storage {

    constructor() {}

    /** @protected */
    prefix = 'espo'

    /** @protected */
    storageObject = localStorage

    /**
     * @private
     * @param {string} type
     * @returns {string}
     */
    composeFullPrefix(type) {
        return this.prefix + '-' + type;
    }

    /**
     * @private
     * @param {string} type
     * @param {string} name
     * @returns {string}
     */
    composeKey(type, name) {
        return this.composeFullPrefix(type) + '-' + name;
    }

    /**
     * @private
     * @param {string} type
     */
    checkType(type) {
        if (
            typeof type === 'undefined' &&
            toString.call(type) !== '[object String]' || type === 'cache'
        ) {
            throw new TypeError("Bad type \"" + type + "\" passed to Espo.Storage.");
        }
    }

    /**
     * Has a value.
     *
     * @param {string} type A type (category).
     * @param {string} name A name.
     * @returns {boolean}
     */
    has(type, name) {
        this.checkType(type);

        const key = this.composeKey(type, name);

        return this.storageObject.getItem(key) !== null;
    }

    /**
     * Get a value.
     *
     * @param {string} type A type (category).
     * @param {string} name A name.
     * @returns {*} Null if not stored.
     */
    get(type, name) {
        this.checkType(type);

        const key = this.composeKey(type, name);

        let stored;

        try {
            stored = this.storageObject.getItem(key);
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
            else if (stored[0] === "{" || stored[0] === "[") { // for backward compatibility
                try {
                    result = JSON.parse(stored);
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
     * @param {string} type A type (category).
     * @param {string} name A name.
     * @param {*} value A value.
     */
    set(type, name, value) {
        this.checkType(type);

        if (value === null) {
            this.clear(type, name);

            return;
        }

        const key = this.composeKey(type, name);

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
            this.storageObject.setItem(key, value);
        }
        catch (error) {
            console.error(error);

            return null;
        }
    }

    /**
     * Clear a value.
     *
     * @param {string} type A type (category).
     * @param {string} name A name.
     */
    clear(type, name) {
        let reText;

        if (typeof type !== 'undefined') {
            if (typeof name === 'undefined') {
                reText = '^' + this.composeFullPrefix(type);
            }
            else {
                reText = '^' + this.composeKey(type, name);
            }
        }
        else {
            reText = '^' + this.prefix + '-';
        }

        const re = new RegExp(reText);

        for (const i in this.storageObject) {
            if (re.test(i)) {
                delete this.storageObject[i];
            }
        }
    }
}

export default Storage;
