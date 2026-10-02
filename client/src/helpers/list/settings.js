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

import {inject} from 'di';
import Storage from 'storage';
import Utils from 'utils';

/**
 * @typedef {Object} ColumnWidth
 * @property {number} value A value.
 * @property {'px'|'%'} unit A unit.
 */

class ListSettingsHelper {

    /**
     * @private
     * @type {Storage}
     */
    @inject(Storage)
    storage

    /**
     * @private
     * @type {boolean}
     */
    useStorage

    /**
     * Note: Do not change the signature for the first 3 parameters.
     * @internal
     *
     * @param {string} entityType
     * @param {string} key A key used for storage.
     * @param {string} userId
     * @param {{useStorage?: boolean}} options
     */
    constructor(entityType, key, userId, options = {}) {
        /** @private */
        this.layoutColumnsKey = `${key}-${entityType}-${userId}`;

        /**
         * @private
         * @type {Object.<string, boolean>}
         */
        this.hiddenColumnMapCache = undefined;

        /**
         * @private
         * @type {Object.<string, ColumnWidth>}
         */
        this.columnWidthMapCache = undefined;

        /**
         * @private
         * @type {boolean|undefined}
         */
        this.columnResize = undefined;

        /**
         * @private
         * @type {function()[]}
         */
        this.columnWidthChangeFunctions = [];

        this.useStorage = options.useStorage ?? true;
    }

    /**
     * @private
     * @param {string} key
     * @return {*}
     */
    getStored(key) {
        if (this.useStorage) {
            return this.storage.get(key, this.layoutColumnsKey);
        }

        return null;
    }

    /**
     * @private
     * @param {string} key
     * @param {*} value
     */
    store(key, value) {
        if (this.useStorage) {
            this.storage.set(key, this.layoutColumnsKey, value);
        }
    }

    /**
     * @private
     * @param {string} key
     */
    clearStored(key) {
        if (this.useStorage) {
            this.storage.clear(key, this.layoutColumnsKey);
        }
    }

    /**
     * Get a stored hidden column map.
     *
     * @return {Object.<string, boolean>}
     */
    getHiddenColumnMap() {
        if (this.hiddenColumnMapCache) {
            return this.hiddenColumnMapCache;
        }

        this.hiddenColumnMapCache = /** @type {Object} */
            this.getStored('listHiddenColumns') ?? {};

        return this.hiddenColumnMapCache;
    }

    /**
     * Is a column hidden.
     *
     * @param {string} name A name.
     * @param {boolean} [hidden] Is hidden by default.
     * @return {boolean}
     * @since 9.0.0
     */
    isColumnHidden(name, hidden) {
        const hiddenMap = this.getHiddenColumnMap();

        if (hiddenMap[name]) {
            return true;
        }

        if (!hidden) {
            return false;
        }

        if (!(name in hiddenMap)) {
            return true;
        }

        return hiddenMap[name];
    }

    /**
     * Is column resize enabled.
     *
     * @return {boolean}
     * @since 9.0.0
     */
    getColumnResize() {
        if (this.columnResize === undefined) {
            this.columnResize = this.getStored('listColumnResize') ?? false;
        }

        return this.columnResize;
    }

    /**
     * Store column width editable.
     *
     * @param {boolean} columnResize
     */
    storeColumnResize(columnResize) {
        this.columnResize = columnResize;

        this.store('listColumnResize', columnResize);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * Clear column width editable.
     */
    clearColumnResize() {
        this.columnResize = undefined;

        this.clearStored('listColumnResize');
    }

    /**
     * Store a hidden column map.
     *
     * @param {Object.<string, boolean>} map
     */
    storeHiddenColumnMap(map) {
        this.hiddenColumnMapCache = Utils.cloneDeep(map);

        this.store('listHiddenColumns', map);
    }

    /**
     * Clear a hidden column map in the storage.
     */
    clearHiddenColumnMap() {
        this.hiddenColumnMapCache = undefined;

        this.clearStored('listHiddenColumns');
    }

    /**
     * Get a stored column width map.
     *
     * @return {Object.<string, ColumnWidth>}
     */
    getColumnWidthMap() {
        if (this.columnWidthMapCache) {
            return this.columnWidthMapCache;
        }

        this.columnWidthMapCache = /** @type {Object} */
            this.getStored('listColumnsWidths') ?? {};

        return this.columnWidthMapCache;
    }

    /**
     * Store a column width map.
     *
     * @param {Object.<string, ColumnWidth>} map
     */
    storeColumnWidthMap(map) {
        this.columnWidthMapCache = Utils.cloneDeep(map);

        this.store('listColumnsWidths', map);
    }

    /**
     * Clear a column width map in the storage.
     */
    clearColumnWidthMap() {
        this.columnWidthMapCache = undefined;

        this.clearStored('listColumnsWidths');
    }

    /**
     * Set a column width.
     *
     * @param {string} name A column name.
     * @param {ColumnWidth} width Width data.
     */
    storeColumnWidth(name, width) {
        if (!this.columnWidthMapCache) {
            this.columnWidthMapCache = {};
        }

        this.columnWidthMapCache[name] = width;

        this.storeColumnWidthMap(this.columnWidthMapCache);

        for (const handler of this.columnWidthChangeFunctions) {
            handler();
        }
    }

    /**
     * Subscribe to a column width change.
     *
     * @param {function()} handler A handler.
     */
    subscribeToColumnWidthChange(handler) {
        this.columnWidthChangeFunctions.push(handler);
    }

    /**
     * Unsubscribe from a column width change.
     *
     * @param {function()} handler A handler.
     */
    unsubscribeFromColumnWidthChange(handler) {
        const index = this.columnWidthChangeFunctions.findIndex(it => handler === it);

        if (!~index) {
            return;
        }

        this.columnWidthChangeFunctions.splice(index, 1);
    }
}

export default ListSettingsHelper;
