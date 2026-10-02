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

export default class LayoutPanelAttributesView extends ModalView {

    templateContent = `
        <div class="panel panel-default no-side-margin">
            <div class="panel-body">
                <div class="edit-container">{{{edit}}}</div>
            </div>
        </div>
    `

    className = 'dialog dialog-record'

    shortcutKeys = {
        /** @this LayoutPanelAttributesView */
        'Control+Enter': function (e) {
            if (
                document.activeElement instanceof HTMLInputElement ||
                document.activeElement instanceof HTMLTextAreaElement
            ) {
                document.activeElement.dispatchEvent(new Event('change', {bubbles: true}));
            }

            this.actionSave();

            e.preventDefault();
            e.stopPropagation();
        },
    }

    setup() {
        this.buttonList = [
            {
                name: 'save',
                text: this.translate('Apply'),
                style: 'primary',
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        const model = new Model();

        model.name = 'LayoutManager';
        model.set(this.options.attributes || {});

        const attributeList = this.options.attributeList;
        const attributeDefs = this.options.attributeDefs;

        this.createView('edit', 'views/admin/layouts/record/edit-attributes', {
            selector: '.edit-container',
            attributeList: attributeList,
            attributeDefs: attributeDefs,
            model: model,
            dynamicLogicDefs: this.options.dynamicLogicDefs,
        });
    }

    actionSave() {
        const editView = /** @type {import('views/record/edit').default} */
            this.getView('edit');

        const attrs = editView.fetch();

        editView.model.set(attrs, {silent: true});

        if (editView.validate()) {
            return;
        }

        const attributes = editView.model.attributes;

        this.trigger('after:save', attributes);

        return true;
    }
}
