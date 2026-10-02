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

export default class extends EnumFieldView {

    getAttributeList() {
        return [
            ...super.getAttributeList(),
            'entityType',
        ];
    }

    setup() {
        super.setup();

        this.model.onChange({
            owner: this,
            attributes: ['entityType'],
            callback: () => this.setupTranslation(),
        });
    }

    setupTranslation() {
        const entityType = this.model.attributes.entityType;

        if (!entityType) {
            this.setTranslatedOptions({});

            return;
        }

        const field = this.getMetadata().get(`scopes.${entityType}.statusField`);

        if (!field) {
            this.setTranslatedOptions({});

            return;
        }

        this.setTranslatedOptions({
            [field]: this.translate(field, 'fields', entityType),
        });
    }
}
