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

/** @module helpers/misc/field-language */

/**
 * A field-language util.
 */
class FieldLanguage {

    /**
     * @param {module:metadata} metadata A metadata.
     * @param {module:language} language A language.
     */
    constructor(metadata, language) {
        /**
         * @private
         * @type {module:metadata}
         */
        this.metadata = metadata;

        /**
         * @private
         * @type {module:language}
         */
        this.language = language;
    }

    /**
     * Translate an attribute.
     *
     * @param {string} scope A scope.
     * @param {string} name An attribute name.
     * @returns {string}
     */
    translateAttribute(scope, name) {
        let label = this.language.translate(name, 'fields', scope);

        if (name.indexOf('Id') === name.length - 2) {
            const baseField = name.slice(0, name.length - 2);

            if (this.metadata.get(['entityDefs', scope, 'fields', baseField])) {
                label = this.language.translate(baseField, 'fields', scope) +
                    ' (' + this.language.translate('id', 'fields') + ')';
            }
        }
        else if (name.indexOf('Name') === name.length - 4) {
            const baseField = name.slice(0, name.length - 4);

            if (this.metadata.get(['entityDefs', scope, 'fields', baseField])) {
                label = this.language.translate(baseField, 'fields', scope) +
                    ' (' + this.language.translate('name', 'fields') + ')';
            }
        }
        else if (name.indexOf('Type') === name.length - 4) {
            const baseField = name.slice(0, name.length - 4);

            if (this.metadata.get(['entityDefs', scope, 'fields', baseField])) {
                label = this.language.translate(baseField, 'fields', scope) +
                    ' (' + this.language.translate('type', 'fields') + ')';
            }
        }

        if (name.indexOf('Ids') === name.length - 3) {
            const baseField = name.slice(0, name.length - 3);

            if (this.metadata.get(['entityDefs', scope, 'fields', baseField])) {
                label = this.language.translate(baseField, 'fields', scope) +
                    ' (' + this.language.translate('ids', 'fields') + ')';
            }
        }
        else if (name.indexOf('Names') === name.length - 5) {
            const baseField = name.slice(0, name.length - 5);

            if (this.metadata.get(['entityDefs', scope, 'fields', baseField])) {
                label = this.language.translate(baseField, 'fields', scope) +
                    ' (' + this.language.translate('names', 'fields') + ')';
            }
        }
        else if (name.indexOf('Types') === name.length - 5) {
            const baseField = name.slice(0, name.length - 5);

            if (this.metadata.get(['entityDefs', scope, 'fields', baseField])) {
                label = this.language.translate(baseField, 'fields', scope) +
                    ' (' + this.language.translate('types', 'fields') + ')';
            }
        }

        return label;
    }
}

export default FieldLanguage;
