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
import Helper from 'helpers/misc/foreign-field';

class ForeignEnumFieldView extends EnumFieldView {

    type = 'foreign'

    /**
     * @private
     * @type {string}
     */
    foreignEntityType

    setup() {
        const helper = new Helper(this);
        const foreignParams = helper.getForeignParams();

        for (const param in foreignParams) {
            this.params[param] = foreignParams[param];
        }

        this.foreignEntityType = helper.getEntityType();

        super.setup();
    }

    setupOptions() {
        const field = this.params.field;
        const link = this.params.link;

        if (!field || !link) {
            return;
        }

        let optionsPath = this.params.optionsPath;
        const optionsReference = this.params.optionsReference;
        let options = this.params.options;
        let style = this.params.style;

        let sourceEntityType = this.foreignEntityType;
        let sourceField = field;

        if (!optionsPath && optionsReference) {
            const [refEntityType, refField] = optionsReference.split('.');

            optionsPath = `entityDefs.${refEntityType}.fields.${refField}.options`;

            sourceEntityType = refEntityType;
            sourceField = field;

            style = this.getMetadata().get(`entityDefs.${sourceEntityType}.fields.${sourceField}.style`) ?? {};
        }

        if (optionsPath) {
            options = this.getMetadata().get(optionsPath);
        }

        this.params.options = Espo.Utils.clone(options) ?? [];
        this.styleMap = style ?? {};

        const pairs = this.params.options
            .map(item => [item, this.getLanguage().translateOption(item, sourceField, sourceEntityType)])

        this.translatedOptions = Object.fromEntries(pairs);
    }
}

export default ForeignEnumFieldView;
