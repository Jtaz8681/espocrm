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

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:entityType', () => {
            this.setupOptions();
            this.reRender();
        });
    }

    setupOptions() {
        const entityType = this.model.attributes.entityType ??
            this.getMetadata().get(['dashlets', this.dataObject.dashletName, 'entityType']);

        const scope = entityType;

        if (!entityType) {
            this.params.options = [];

            return;
        }

        /** @type {Record<string, Record>} */
        const fieldDefs = this.getMetadata().get(`entityDefs.${scope}.fields`) || {};

        const orderableFieldList = Object.keys(fieldDefs)
            .filter(item => {
                if (fieldDefs[item].orderDisabled || fieldDefs[item].utility) {
                    return false;
                }

                return true;
            })
            .sort((v1, v2) => {
                return this.translate(v1, 'fields', scope).localeCompare(this.translate(v2, 'fields', scope));
            });

        const translatedOptions = {};

        orderableFieldList.forEach(item => {
            translatedOptions[item] = this.translate(item, 'fields', scope);
        });

        this.params.options = orderableFieldList;
        this.translatedOptions = translatedOptions;
    }
}
