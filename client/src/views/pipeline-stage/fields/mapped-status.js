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

import EnumFieldView from 'views/fields/enum';

// noinspection JSUnusedGlobalSymbols
export default class PipelineStageMappedStatusFieldView extends EnumFieldView {

    setupOptions() {
        const entityType = this.model.attributes.entityType;
        const field = this.model.attributes.field;

        if (!entityType || !field) {
            return;
        }

        let sourceEntityType = entityType;
        let sourceField = field;

        const params = this.getMetadata().get(`entityDefs.${entityType}.fields.${field}`) ?? {};

        let optionsPath = params.optionsPath;
        const optionsReference = params.optionsReference;
        /** @var string[] */
        let options = params.options ?? [];
        const style = params.style;

        if (!optionsPath && optionsReference) {
            const [refEntityType, refField] = optionsReference.split('.');

            optionsPath = `entityDefs.${refEntityType}.fields.${refField}.options`;

            sourceEntityType = refEntityType;
            sourceField = refField;
        }

        if (optionsPath) {
            options = this.getMetadata().get(optionsPath);
        }

        options = options.filter(it => it);

        this.params.options = Espo.Utils.clone(options);
        this.styleMap = style ?? {};

        const pairs = this.params.options
            .map(item => [item, this.getLanguage().translateOption(item, sourceField, sourceEntityType)])

        this.translatedOptions = Object.fromEntries(pairs);
    }
}
