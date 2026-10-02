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

/** @module layout-manager */

import {Events} from 'bullbone';

/**
 * A layout manager.
 *
 * @mixes Bull.Events
 */
class LayoutManager {

    /**
     * @param {module:cache|null} [cache] A cache.
     * @param {string} [applicationId] An application ID.
     * @param {string} [userId] A user ID.
     */
    constructor(cache, applicationId, userId) {

        /**
         * @private
         * @type {module:cache|null}
         */
        this.cache = cache || null;

        /**
         * @private
         * @type {string}
         */
        this.applicationId = applicationId || 'espocrm';

        /**
         * @private
         * @type {string|null}
         */
        this.userId = userId || null;

        /**
         * @private
         * @type {Object}
         */
        this.data = {};

        /** @private */
        this.ajax = Espo.Ajax;

        /**
         * @private
         * @type {Object.<string, module:ajax~AjaxPromise>}
         */
        this.fetchPromises = {};
    }

    /**
     * Set a user ID. To be used for the cache purpose.
     *
     * @param {string} userId A user ID.
     * @internal
     * @todo Throw an exception if already set.
     */
    setUserId(userId) {
        this.userId = userId
    }

    /**
     * @private
     * @param {string} scope
     * @param {string} type
     * @returns {string}
     */
    getKey(scope, type) {
        if (this.userId) {
            return `${this.applicationId}-${this.userId}-${scope}-${type}`;
        }

        return `${this.applicationId}-${scope}-${type}`;
    }

    /**
     * @private
     * @param {string} scope
     * @param {string} type
     * @param {string} [setId]
     * @returns {string}
     */
    getUrl(scope, type, setId) {
        let url = `${scope}/layout/${type}`;

        if (setId) {
            url += `/${setId}`;
        }

        return url;
    }

    /**
     * @callback module:layout-manager~getCallback
     *
     * @param {*} layout A layout.
     */

    /**
     * Get a layout.
     *
     * @param {string} scope A scope (entity type).
     * @param {string} type A layout type (name).
     * @param {module:layout-manager~getCallback} callback
     * @param {boolean} [cache=true] Use cache.
     */
    get(scope, type, callback, cache) {
        if (typeof cache === 'undefined') {
            cache = true;
        }

        if (!callback) {
            callback = () => {};
        }

        const key = this.getKey(scope, type);

        if (cache && key in this.data) {
            callback(this.data[key]);

            return;
        }

        if (this.cache && cache) {
            const cached = this.cache.get('app-layout', key);

            if (cached) {
                callback(cached);

                this.data[key] = cached;

                return;
            }
        }

        if (key in this.fetchPromises) {
            this.fetchPromises[key].then(layout => callback(layout));

            return;
        }

        this.fetchPromises[key] = this.ajax.getRequest(this.getUrl(scope, type))
            .then(layout => {
                callback(layout);

                this.data[key] = layout;

                if (this.cache) {
                    this.cache.set('app-layout', key, layout);
                }

                return layout;
            })
            .finally(() => delete this.fetchPromises[key]);
    }

    /**
     * Get an original layout.
     *
     * @param {string} scope A scope (entity type).
     * @param {string} type A layout type (name).
     * @param {string} [setId]
     * @param {module:layout-manager~getCallback} callback
     */
    getOriginal(scope, type, setId, callback) {
        let url = 'Layout/action/getOriginal?scope='+scope+'&name='+type;

        if (setId) {
            url += '&setId=' + setId;
        }

        Espo.Ajax.getRequest(url)
            .then(layout => {
                if (typeof callback === 'function') {
                    callback(layout);
                }
            });
    }

    /**
     * Store and set a layout.
     *
     * @param {string} scope A scope (entity type).
     * @param {string} type A type (name).
     * @param {*} layout A layout.
     * @param {Function} callback A callback.
     * @param {string} [setId] A set ID.
     * @returns {Promise}
     */
    set(scope, type, layout, callback, setId) {
        return Espo.Ajax.putRequest(this.getUrl(scope, type, setId), layout)
            .then(() => {
                const key = this.getKey(scope, type);

                if (this.cache && key) {
                    this.cache.clear('app-layout', key);
                }

                delete this.data[key];

                this.trigger('sync');

                if (typeof callback === 'function') {
                    callback();
                }
            });
    }

    /**
     * Reset a layout to default.
     *
     * @param {string} scope A scope (entity type).
     * @param {string} type A type (name).
     * @param {Function} callback A callback.
     * @param {string} [setId] A set ID.
     */
    resetToDefault(scope, type, callback, setId) {
        Espo.Ajax
            .postRequest('Layout/action/resetToDefault', {
                scope: scope,
                name: type,
                setId: setId,
            })
            .then(() => {
                const key = this.getKey(scope, type);

                if (this.cache) {
                    this.cache.clear('app-layout', key);
                }

                delete this.data[key];

                this.trigger('sync');

                if (typeof callback === 'function') {
                    callback();
                }
            });
    }

    /**
     * Clear loaded data.
     */
    clearLoadedData() {
        this.data = {};
    }
}

Object.assign(LayoutManager.prototype, Events);

export default LayoutManager;
