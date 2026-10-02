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

/** @module helpers/misc/stored-text-search */

export default class {
    /**
     * @param {module:storage} storage
     * @param {string} scope
     * @param {Number} [maxCount]
     */
    constructor(scope, storage, maxCount) {
        this.scope = scope;
        this.storage = storage;
        this.key = 'textSearches';
        this.maxCount = maxCount || 100;
        /** @type {string[]|null} */
        this.list = null;
    }

    /**
     * Match.
     *
     * @param {string} text
     * @param {Number} [limit]
     * @return {string[]}
     */
    match(text, limit) {
        text = text.toLowerCase().trim();

        const list = this.get();
        const matchedList = [];

        for (const item of list) {
            if (item.toLowerCase().startsWith(text)) {
                matchedList.push(item);
            }

            if (limit !== undefined && matchedList.length === limit) {
                break;
            }
        }

        return matchedList;
    }

    /**
     * Get stored text filters.
     *
     * @private
     * @return {string[]}
     */
    get() {
        if (this.list === null) {
            this.list = this.getFromStorage();
        }

        return this.list;
    }

    /**
     * @private
     * @return {string[]}
     */
    getFromStorage() {
        /** @var {string[]} */
        return this.storage.get(this.key, this.scope) || [];
    }

    /**
     * Store a text filter.
     *
     * @param {string} text
     */
    store(text) {
        text = text.trim();

        let list = this.getFromStorage();

        const index = list.indexOf(text);

        if (index !== -1) {
            list.splice(index, 1);
        }

        list.unshift(text);

        if (list.length > this.maxCount) {
            list = list.slice(0, this.maxCount);
        }

        this.list = list;
        this.storage.set(this.key, this.scope, list);
    }

    /**
     * Remove a text filter.
     *
     * @param {string} text
     */
    remove(text) {
        text = text.trim();

        const list = this.getFromStorage();

        const index = list.indexOf(text);

        if (index === -1) {
            return;
        }

        list.splice(index, 1);

        this.list = list;
        this.storage.set(this.key, this.scope, list);
    }
}
