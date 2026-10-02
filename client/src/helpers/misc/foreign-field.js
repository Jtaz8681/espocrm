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

/** @module helpers/misc/foreign-field */

export default class {

    /**
     * @private
     * @type {string}
     */
    entityType

    /**
     * @param {module:views/fields/base} view A field view.
     */
    constructor(view) {
        /**
         * @private
         * @type {module:views/fields/base}
         */
        this.view = view;

        const metadata = view.getMetadata();
        const model = view.model;
        const field = view.params.field;
        const link = view.params.link;

        const entityType = metadata.get(['entityDefs', model.entityType, 'links', link, 'entity']) ||
            model.entityType;

        this.entityType = entityType;

        const fieldDefs = metadata.get(['entityDefs', entityType, 'fields', field]) || {};
        const type = fieldDefs.type;

        const ignoreList = [
            'default',
            'audited',
            'readOnly',
            'required',
        ];

        /** @private */
        this.foreignParams = {};

        view.getFieldManager().getParamList(type).forEach(defs => {
            const name = defs.name;

            if (ignoreList.includes(name)) {
                return;
            }

            this.foreignParams[name] = fieldDefs[name] || null;
        });
    }

    /**
     * @return {Object.<string, *>}
     */
    getForeignParams() {
        return Espo.Utils.cloneDeep(this.foreignParams);
    }

    /**
     * @return {string}
     */
    getEntityType() {
        return this.entityType;
    }
}
