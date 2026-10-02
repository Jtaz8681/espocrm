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

import ModalView from 'views/modal';
import Model from 'model';

export default class extends ModalView {

    templateContent = '<div class="attribute" data-name="attribute">{{{attribute}}}</div>'

    backdrop = true

    setup() {
        this.headerText = this.translate('Attribute');
        this.scope = this.options.scope;

        const model = new Model();

        this.createView('attribute', 'views/admin/formula/fields/attribute', {
            selector: '[data-name="attribute"]',
            model: model,
            mode: 'edit',
            scope: this.scope,
            defs: {
                name: 'attribute',
                params: {}
            },
            attributeList: this.options.attributeList,
        }, view => {
            this.listenTo(view, 'change', () => {
                const list = model.get('attribute') || [];

                if (!list.length) {
                    return;
                }

                this.trigger('add', list[0]);
            });
        });
    }
}
