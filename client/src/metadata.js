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

/** @module metadata */

import {Events} from 'bullbone';

/**
 * Application metadata.
 *
 * @mixes Bull.Events
 */
class Metadata {

    /**
     * Application metadata.
     *
     * @param {module:cache} [cache] A cache.
     */
    constructor(cache) {
        /**
         * @private
         * @type {module:cache|null}
         */
        this.cache = cache || null;

        /**
         * @private
         * @type {Object}
         */
        this.data = {};
    }

    /** @private */
    url = 'Metadata'

    /**
     * Load from cache or the backend (if not yet cached).
     *
     * @param {Function|null} [callback] Deprecated. Use a promise.
     * @param {boolean} [disableCache=false] Deprecated.
     * @returns {Promise}
     */
    load(callback, disableCache) {
        if (!disableCache) {
            if (this.loadFromCache()) {
                this.trigger('sync');

                if (callback) {
                    callback();
                }

                return Promise.resolve();
            }
        }

        return this.fetch()
            .then(() => {
                if (callback) {
                    callback();
                }
            });
    }

    /**
     * Load from the server.
     *
     * @returns {Promise}
     */
    loadSkipCache() {
        return this.load(null, true);
    }

    /**
     * @private
     * @returns {Promise}
     */
    fetch() {
        return Espo.Ajax.getRequest(this.url)
            .then(data => {
                this.data = data;
                this.storeToCache();
                this.trigger('sync');
            });
    }

    /**
     * Get a value.
     *
     * @param {string[]|string} path A key path.
     * @param {*} [defaultValue] A value to return if not set.
     * @returns {*} Null if not set.
     */
    get(path, defaultValue) {
        defaultValue = defaultValue || null;

        let arr;

        if (Array && Array.isArray && Array.isArray(path)) {
            arr = path;
        }
        else {
            arr = path.split('.');
        }

        let pointer = this.data;
        let result = defaultValue;

        for (let i = 0; i < arr.length; i++) {
            const key = arr[i];

            if (pointer == null || !(key in pointer)) {
                result = defaultValue;

                break;
            }

            if (arr.length - 1 === i) {
                result = pointer[key];
            }

            pointer = pointer[key];
        }

        return result;
    }

    /**
     * @private
     * @returns {boolean|null} True if success.
     */
    loadFromCache() {
        if (this.cache) {
            const cached = this.cache.get('app', 'metadata');

            if (cached) {
                this.data = cached;

                return true;
            }
        }

        return null;
    }

    /** @private */
    storeToCache() {
        if (this.cache) {
            this.cache.set('app', 'metadata', this.data);
        }
    }

    /**
     * Clear cache.
     */
    clearCache() {
        if (!this.cache) {
            return;
        }

        this.cache.clear('app', 'metadata');
    }

    /**
     * Get a scope list.
     *
     * @returns {string[]}
     */
    getScopeList () {
        const scopes = this.get('scopes') || {};
        const scopeList = [];

        for (const scope in scopes) {
            const d = scopes[scope];

            if (d.disabled) {
                continue;
            }

            scopeList.push(scope);
        }

        return scopeList;
    }

    /**
     * Get an object-scope list. An object-scope represents a business entity.
     *
     * @returns {string[]}
     */
    getScopeObjectList () {
        const scopes = this.get('scopes') || {};
        const scopeList = [];

        for (const scope in scopes) {
            const d = scopes[scope];

            if (d.disabled) {
                continue;
            }

            if (!d.object) {
                continue;
            }

            scopeList.push(scope);
        }

        return scopeList;
    }

    /**
     * Get an entity-scope list. Scopes that represents entities.
     *
     * @returns {string[]}
     */
    getScopeEntityList () {
        const scopes = this.get('scopes') || {};
        const scopeList = [];

        for (const scope in scopes) {
            const d = scopes[scope];

            if (d.disabled) {
                continue;
            }

            if (!d.entity) {
                continue;
            }

            scopeList.push(scope);
        }

        return scopeList;
    }

    /**
     * Do not use.
     *
     * @internal
     * @param {Record} data
     */
    setData(data) {
        this.data = data;
    }
}

Object.assign(Metadata.prototype, Events);

export default Metadata;
