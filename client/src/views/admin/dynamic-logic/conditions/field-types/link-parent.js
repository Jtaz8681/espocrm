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

export default class extends DynamicLogicConditionFieldTypeBaseView {

    fetch() {
        /** @type {import('views/fields/base').default} */
        const valueView = this.getView('value');

        let item;

        if (valueView) {
            valueView.fetchToModel();
        }

        if (this.type === 'equals' || this.type === 'notEquals') {
            const values = {};

            values[this.field + 'Id'] = valueView.model.get(this.field + 'Id');
            values[this.field + 'Name'] = valueView.model.get(this.field + 'Name');
            values[this.field + 'Type'] = valueView.model.get(this.field + 'Type');

            if (this.type === 'equals') {
                item = {
                    type: 'and',
                    value: [
                        {
                            type: 'equals',
                            attribute: this.field + 'Id',
                            value: valueView.model.get(this.field + 'Id')
                        },
                        {
                            type: 'equals',
                            attribute: this.field + 'Type',
                            value: valueView.model.get(this.field + 'Type')
                        }
                    ],
                    data: {
                        field: this.field,
                        type: 'equals',
                        values: values
                    }
                };
            } else {
                item = {
                    type: 'or',
                    value: [
                        {
                            type: 'notEquals',
                            attribute: this.field + 'Id',
                            value: valueView.model.get(this.field + 'Id')
                        },
                        {
                            type: 'notEquals',
                            attribute: this.field + 'Type',
                            value: valueView.model.get(this.field + 'Type')
                        }
                    ],
                    data: {
                        field: this.field,
                        type: 'notEquals',
                        values: values
                    }
                };
            }
        } else {
            item = {
                type: this.type,
                attribute: this.field + 'Id',
                data: {
                    field: this.field
                }
            };
        }

        return item;
    }
}
