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

    templateContent = `<div class="field" data-name="field">{{{field}}}</div>`


    setup() {
        this.addActionHandler('addField', (e, target) => {
            this.trigger('add-field', target.dataset.name);
        })

        this.headerText = this.translate('Add Field');
        this.scope = this.options.scope;

        const model = new Model();

        this.createView('field', 'views/admin/dynamic-logic/fields/field', {
            selector: '[data-name="field"]',
            model: model,
            mode: 'edit',
            scope: this.scope,
            defs: {
                name: 'field',
                params: {},
            },
        }, view => {
            this.listenTo(view, 'change', () => {
                const list = model.get('field') || [];

                if (!list.length) {
                    return;
                }

                this.trigger('add-field', list[0]);
            });
        });
    }
}
