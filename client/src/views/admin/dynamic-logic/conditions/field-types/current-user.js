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
import Model from 'model';

export default class extends DynamicLogicConditionFieldTypeBaseView {

    getValueViewName() {
        return 'views/fields/user';
    }

    getValueFieldName() {
        return 'link';
    }

    createModel() {
        const model = new Model();

        model.setDefs({
            fields: {
                link: {
                    type: 'link',
                    entity: 'User',
                },
            }
        });

        return Promise.resolve(model);
    }

    populateValues() {
        if (this.itemData.attribute) {
            this.model.set('linkId', this.itemData.value);
        }

        const name = (this.additionalData.values || {}).name;

        this.model.set('linkName', name);
    }

    translateLeftString() {
        return '$' + this.translate('User', 'scopeNames');
    }

    fetch() {
        /** @type {import('views/fields/base').default} */
        const valueView = this.getView('value');

        valueView.fetchToModel();

        return {
            type: this.type,
            attribute: '$user.id',
            data: {
                values: {
                    name: this.model.get('linkName'),
                },
            },
            value: this.model.get('linkId'),
        };
    }
}
