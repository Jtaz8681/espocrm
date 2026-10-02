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

import DynamicLogicConditionFieldTypeBaseView from 'views/admin/dynamic-logic/conditions/field-types/base';

export default class DynamicLogicConditionFieldTypeLinkMultipleView extends DynamicLogicConditionFieldTypeBaseView {

    getValueFieldName() {
        return this.name;
    }

    getValueViewName() {
        return 'views/fields/link';
    }

    // noinspection JSUnusedGlobalSymbols
    createValueViewContains() {
        this.createLinkValueField();
    }

    // noinspection JSUnusedGlobalSymbols
    createValueViewNotContains() {
        this.createLinkValueField();
    }

    createLinkValueField() {
        const viewName = 'views/fields/link';
        const fieldName = 'link';

        this.createView('value', viewName, {
            model: this.model,
            name: fieldName,
            selector: '.value-container',
            mode: 'edit',
            readOnlyDisabled: true,
            foreignScope: this.getMetadata().get(['entityDefs', this.scope, 'fields', this.field, 'entity']) ||
                this.getMetadata().get(['entityDefs', this.scope, 'links', this.field, 'entity']),
        }, view => {
            if (this.isRendered()) {
                view.render();
            }
        });
    }

    fetch() {
        /** @type {import('views/fields/base').default} */
        const valueView = this.getView('value');

        const item = {
            type: this.type,
            attribute: this.field + 'Ids',
            data: {
                field: this.field,
            },
        };

        if (valueView) {
            valueView.fetchToModel();

            item.value = this.model.get('linkId');

            const values = {};

            values['linkName'] = this.model.get('linkName');
            values['linkId'] = this.model.get('linkId');

            item.data.values = values;
        }

        return item;
    }
}
