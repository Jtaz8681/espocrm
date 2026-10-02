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

/** @module cache */

/**
 * Cache for source and resource files.
 */
class Cache {

    /**
     * @param {Number} [cacheTimestamp] A cache timestamp.
     */
    constructor(cacheTimestamp) {
        this.basePrefix = this.prefix;

        if (cacheTimestamp) {
            this.prefix =  this.basePrefix + '-' + cacheTimestamp;
        }

        if (!this.get('app', 'timestamp')) {
            this.storeTimestamp();
        }
    }

    /** @private */
    prefix = 'cache'

    /**
     * Handle actuality. Clears cache if not actual.
     *
     * @param {Number} cacheTimestamp A cache timestamp.
     */
    handleActuality(cacheTimestamp) {
        const storedTimestamp = this.getCacheTimestamp();

        if (storedTimestamp) {
            if (storedTimestamp !== cacheTimestamp) {
                this.clear();
                this.set('app', 'cacheTimestamp', cacheTimestamp);
                this.storeTimestamp();
            }

            return;
        }

        this.clear();
        this.set('app', 'cacheTimestamp', cacheTimestamp);
        this.storeTimestamp();
    }

    /**
     * Get a cache timestamp.
     *
     * @returns {number}
     */
    getCacheTimestamp() {
        return parseInt(this.get('app', 'cacheTimestamp') || 0);
    }

    /**
     * @todo Revise whether is needed.
     */
    storeTimestamp() {
        const frontendCacheTimestamp = Date.now();

        this.set('app', 'timestamp', frontendCacheTimestamp);
    }

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
        if (typeof type === 'undefined' && toString.call(type) !== '[object String]') {
            throw new TypeError("Bad type \"" + type + "\" passed to Cache().");
        }
    }

    /**
     * Get a stored value.
     *
     * @param {string} type A type/category.
     * @param {string} name A name.
     * @returns {string|null} Null if no stored value.
     */
    get(type, name) {
        this.checkType(type);

        const key = this.composeKey(type, name);

        let stored;

        try {
            stored = localStorage.getItem(key);
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
     * Store a value.
     *
     * @param {string} type A type/category.
     * @param {string} name A name.
     * @param {any} value A value.
     */
    set(type, name, value) {
        this.checkType(type);

        const key = this.composeKey(type, name);

        if (value instanceof Object || Array.isArray(value)) {
            value = '__JSON__:' + JSON.stringify(value);
        }

        try {
            localStorage.setItem(key, value);
        }
        catch (error) {
            console.log('Local storage limit exceeded.');
        }
    }

    /**
     * Clear a stored value.
     *
     * @param {string} [type] A type/category.
     * @param {string} [name] A name.
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
            reText = '^' + this.basePrefix + '-';
        }

        const re = new RegExp(reText);

        for (const i in localStorage) {
            if (re.test(i)) {
                delete localStorage[i];
            }
        }
    }
}

export default Cache;
