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

/** @module field-manager */

/**
 * Utility for getting field related meta information.
 */
class FieldManager {

    /**
     * Utility for getting field related meta information.
     *
     * @param {Object} [defs] Field type definitions (metadata > fields).
     * @param {module:metadata} [metadata] Metadata.
     * @param {module:acl-manager} [acl] An ACL.
     */
    constructor(defs, metadata, acl) {

        /**
         * @typedef {Object} FieldManager~defs
         * @property {string[]} [actualFields]
         * @property {string[]} [notActualFields]
         * @property {'suffix'|'prefix'} [naming]
         * @property {Object.<string, Object.<string, *>>} [params]
         * @property {boolean} [filter]
         * @property {boolean} [notMergeable]
         * @property {string} [view]
         */


        /**
         * @public
         * @internal
         * @type {FieldManager~defs}
         */
        this.defs = defs || /** @type {FieldManager~defs} */ {};

        /**
         * @public
         * @internal
         * @type {module:metadata}
         */
        this.metadata = metadata;

        /**
         * @public
         * @internal
         * @type {module:acl-manager}
         */
        this.acl = acl;
    }

    /**
     * Get a list of parameters for a specific field type.
     *
     * @param {string} fieldType A field type.
     * @returns {Object.<string, *>[]}
     */
    getParamList(fieldType) {
        if (fieldType in this.defs) {
            return this.defs[fieldType].params || [];
        }

        return [];
    }

    /**
     * Whether search filters are allowed for a field type.
     *
     * @param {string} fieldType A field type.
     * @returns {boolean}
     */
    checkFilter(fieldType) {
        if (fieldType in this.defs) {
            if ('filter' in this.defs[fieldType]) {
                return this.defs[fieldType].filter;
            }

            return false;
        }

        return false;
    }

    /**
     * Whether a merge operation is allowed for a field type.
     *
     * @param {string} fieldType A field type.
     * @returns {boolean}
     */
    isMergeable(fieldType) {
        if (fieldType in this.defs) {
            return !this.defs[fieldType].notMergeable;
        }

        return false;
    }

    /**
     * Get a list of attributes of an entity type.
     *
     * @param {string} entityType An entity type.
     * @param {module:field-manager~FieldFilters} [options] Filters.
     * @returns {string[]}
     */
    getEntityTypeAttributeList(entityType, options) {
        const list = [];

        const defs = this.metadata.get(`entityDefs.${entityType}.fields`) || {};

        this.getEntityTypeFieldList(entityType, options).forEach(field => {
            const fieldDefs = /** @type {Record} */defs[field] || {};

            this.getAttributeList(fieldDefs.type, field).forEach(attr => {
                if (!list.includes(attr)) {
                    list.push(attr);
                }
            });
        });

        return list;
    }

    /**
     * Get a list of actual attributes by a given field type and field name.
     * Non-actual attributes contains data that for a representation-only purpose.
     * E.g. `accountId` is actual, `accountName` is non-actual.
     *
     * @param {string} fieldType A field type.
     * @param {string} fieldName A field name.
     * @returns {string[]}
     */
    getActualAttributeList(fieldType, fieldName) {
        const output = [];

        if (!(fieldType in this.defs)) {
            return [];
        }

        if ('actualFields' in this.defs[fieldType]) {
            const actualFields = this.defs[fieldType].actualFields;

            let naming = 'suffix';

            if ('naming' in this.defs[fieldType]) {
                naming = this.defs[fieldType].naming;
            }

            if (naming === 'prefix') {
                actualFields.forEach(it => output.push(it + Espo.Utils.upperCaseFirst(fieldName)));
            } else {
                actualFields.forEach(it => output.push(fieldName + Espo.Utils.upperCaseFirst(it)));
            }
        } else {
            output.push(fieldName);
        }

        return output;
    }

    /**
     * Get a list of non-actual attributes by a given field type and field name.
     * Non-actual attributes contains data that for a representation-only purpose.
     * E.g. `accountId` is actual, `accountName` is non-actual.
     *
     * @param {string} fieldType A field type.
     * @param {string} fieldName A field name.
     * @returns {string[]}
     */
    getNotActualAttributeList(fieldType, fieldName) {
        if (!(fieldType in this.defs)) {
            return [];
        }

        if (!('notActualFields' in this.defs[fieldType])) {
            return [];
        }

        const notActualFields = this.defs[fieldType].notActualFields;

        let naming = 'suffix';

        if ('naming' in this.defs[fieldType]) {
            naming = this.defs[fieldType].naming;
        }

        const output = [];

        if (naming === 'prefix') {
            notActualFields.forEach(it => {
                if (it === '') {
                    output.push(fieldName);
                } else {
                    output.push(it + Espo.Utils.upperCaseFirst(fieldName));
                }
            });
        } else {
            notActualFields.forEach(it => output.push(fieldName + Espo.Utils.upperCaseFirst(it)));
        }

        return output;
    }

    /**
     * Get an attribute list of a specific field.
     *
     * @param {string} entityType An entity type.
     * @param {string} field A field.
     * @returns {string[]}
     */
    getEntityTypeFieldAttributeList(entityType, field) {
        const type = this.metadata.get(['entityDefs', entityType, 'fields', field, 'type']);

        if (!type) {
            return [];
        }

        return _.union(
            this.getAttributeList(type, field),
            this._getEntityTypeFieldAdditionalAttributeList(entityType, field),
            this._getEntityTypeFieldFullNameAdditionalAttributeList(entityType, field),
        );
    }

    /**
     * Get an actual attribute list of a specific field.
     *
     * @param {string} entityType An entity type.
     * @param {string} field A field.
     * @returns {string[]}
     */
    getEntityTypeFieldActualAttributeList(entityType, field) {
        const type = this.metadata.get(['entityDefs', entityType, 'fields', field, 'type']);

        if (!type) {
            return [];
        }

        return _.union(
            this.getActualAttributeList(type, field),
            this._getEntityTypeFieldAdditionalAttributeList(entityType, field),
            this._getEntityTypeFieldFullNameAdditionalAttributeList(entityType, field),
        );
    }

    /**
     * @private
     */
    _getEntityTypeFieldAdditionalAttributeList(entityType, field) {
        const type = this.metadata.get(['entityDefs', entityType, 'fields', field, 'type']);

        if (!type) {
            return [];
        }

        const partList = this.metadata
            .get(['entityDefs', entityType, 'fields', field, 'additionalAttributeList']) || [];

        if (partList.length === 0) {
            return [];
        }

        const isPrefix = (this.defs[type] || {}).naming === 'prefix';

        const list = [];

        partList.forEach(item => {
            if (isPrefix) {
                list.push(item + Espo.Utils.upperCaseFirst(field));

                return;
            }

            list.push(field + Espo.Utils.upperCaseFirst(item));
        });

        return list;
    }

    /**
     * Get a list of attributes by a given field type and field name.
     *
     * @param {string} fieldType A field type.
     * @param {string} fieldName A field name.
     * @returns {string[]}
     */
    getAttributeList(fieldType, fieldName) {
        return _.union(
            this.getActualAttributeList(fieldType, fieldName),
            this.getNotActualAttributeList(fieldType, fieldName)
        );
    }

    /**
     * @typedef {Object} module:field-manager~FieldFilters
     *
     * @property {string} [type] Only of a specific field type.
     * @property {string[]} [typeList] Only of a specific field types.
     * @property {string[]} [ignoreTypeList] Ignore field types.
     * @property {boolean} [onlyAvailable] To exclude disabled, admin-only, internal, forbidden fields.
     * @property {'read'|'edit'} [acl] To exclude fields not accessible for a current user over
     *   a specified access level.
     */

    /**
     * Get a list of fields of a specific entity type.
     *
     * @param {string} entityType An entity type.
     * @param {module:field-manager~FieldFilters} [options] Filters.
     * @returns {string[]}
     */
    getEntityTypeFieldList(entityType, options) {
        /** @type {Record} */
        const fieldDefs = this.metadata.get(['entityDefs', entityType, 'fields']) || {}

        let list = Object.keys(fieldDefs);

        options = options || {};

        let typeList = options.typeList;

        if (!typeList && options.type) {
            typeList = [options.type];
        }

        if (typeList) {
            list = list.filter(item => {
                const type = this.metadata.get(['entityDefs', entityType, 'fields', item, 'type']);

                return typeList.includes(type);
            });
        }

        if (options.ignoreTypeList) {
            list = list.filter(field => {
                const type = (fieldDefs[field] || {}).type;

                return !options.ignoreTypeList.includes(type);
            });
        }

        if (options.onlyAvailable || options.acl) {
            list = list.filter(item => {
                return this.isEntityTypeFieldAvailable(entityType, item);
            });
        }

        if (options.acl) {
            const level = options.acl || 'read';

            const forbiddenEditFieldList = this.acl.getScopeForbiddenFieldList(entityType, level);

            list = list.filter(item => {
                return !forbiddenEditFieldList.includes(item);
            });
        }

        return list;
    }

    /**
     * Get a field parameter value.
     *
     * @param {string} entityType An entity type.
     * @param {string} field A field name.
     * @param {string} param A parameter name.
     * @returns {*}
     */
    getEntityTypeFieldParam(entityType, field, param) {
        return this.metadata.get(['entityDefs', entityType, 'fields', field, param]);
    }

    /**
     * Get a view name/path for a specific field type.
     *
     * @param {string} fieldType A field type.
     * @returns {string}
     */
    getViewName(fieldType) {
        if (fieldType in this.defs) {
            if ('view' in this.defs[fieldType]) {
                return this.defs[fieldType].view;
            }
        }

        return 'views/fields/' + Espo.Utils.camelCaseToHyphen(fieldType);
    }

    /**
     * Check whether a field is not disabled, not utility, not only-admin, not forbidden and not internal.
     *
     * @param {string} entityType An entity type.
     * @param {string} field A field name.
     * @returns {boolean}
     */
    isEntityTypeFieldAvailable(entityType, field) {
        /** @type {Record} */
        const defs = this.metadata.get(['entityDefs', entityType, 'fields', field]) || {};

        if (
            defs.disabled ||
            defs.utility
        ) {
            return false;
        }

        /** @type {Record} */
        const aclDefs = this.metadata.get(['entityAcl', entityType, 'fields', field]) || {};

        if (
            aclDefs.onlyAdmin ||
            aclDefs.forbidden ||
            aclDefs.internal
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param {string} entityType
     * @param {string} field
     * @return {string[]}
     * @private
     */
    _getEntityTypeFieldFullNameAdditionalAttributeList(entityType, field) {
        return this.metadata.get(`entityDefs.${entityType}.fields.${field}.fullNameAdditionalAttributeList`) ?? [];
    }
}

export default FieldManager;
