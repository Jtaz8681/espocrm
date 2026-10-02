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
import Metadata from 'metadata';
import Language from 'language';

/**
 * @since 10.0.0
 */
export default class OptionsProvider {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @private
     * @type {Language}
     */
    @inject(Language)
    language

    /**
     * @param {string} entityType
     * @param {string} field
     * @return {{
     *     name: string,
     *     label: string,
     *     style: string|null,
     * }[]}
     */
    get(entityType, field) {
        /**
         * @type {{
         *     options?: string[],
         *     style?: Record<string, string>,
         *     optionsReference?: string|null,
         *     optionsPath?: string|null
         * }} */
        const params = this.metadata.get(`entityDefs.${entityType}.fields.${field}`);

        if (!params) {
            return [];
        }

        let sourceEntityType = entityType;
        let sourceField = field;
        let styleMap = params.style ?? {};

        let optionsPath = params.optionsPath;

        if (!optionsPath && params.optionsReference) {
            const [refEntityType, refField] = params.optionsReference.split('.');

            optionsPath = `entityDefs.${refEntityType}.fields.${refField}.options`;

            sourceEntityType = refEntityType;
            sourceField = refField;

            styleMap = this.metadata.get(`entityDefs.${refEntityType}.fields.${refField}.style`) ?? {};
        }

        let options = params.options;

        if (optionsPath) {
            options = this.metadata.get(optionsPath);
        }

        return options.map(it => {
            return {
                name: it,
                label: this.language.translateOption(it, sourceField, sourceEntityType),
                style: styleMap[it] ?? null,
            }
        });
    }
}
