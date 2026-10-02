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

import DynamicLogicConditionsStringItemBaseView from 'views/admin/dynamic-logic/conditions-string/item-base';

export default class extends DynamicLogicConditionsStringItemBaseView {

    template = 'admin/dynamic-logic/conditions-string/item-base'

    createValueFieldView() {
        const key = this.getValueViewKey();

        const viewName = 'views/fields/enum';

        this.createView('value', viewName, {
            model: this.model,
            name: this.field,
            selector: `[data-view-key="${key}"]`,
            params: {
                options: this.getMetadata().get(['entityDefs', this.scope, 'fields', this.field, 'options']) || []
            },
            readOnly: true,
        });
    }
}
