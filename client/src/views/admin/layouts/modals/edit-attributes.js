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

export default class LayoutEditAttributesView extends ModalView {

    templateContent = `
        <div class="panel panel-default no-side-margin">
            <div class="panel-body">
                <div class="edit-container">{{{edit}}}</div>
            </div>
        </div>
    `

    className = 'dialog dialog-record'

    shortcutKeys = {
        /** @this LayoutEditAttributesView */
        'Control+Enter': function (e) {
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
                text: this.translate('Cancel'),
            },
        ];

        const model = new Model();

        model.name = 'LayoutManager';

        model.set(this.options.attributes || {});

        this.headerText = undefined;

        if (this.options.languageCategory) {
            this.headerText = this.translate(
                this.options.name,
                this.options.languageCategory,
                this.options.scope
            );
        }

        let attributeList = Espo.Utils.clone(this.options.attributeList || []);

        const filteredAttributeList = [];

        attributeList.forEach(item => {
            const defs = this.options.attributeDefs[item] || {};

            if (defs.readOnly || defs.hidden) {
                return;
            }

            filteredAttributeList.push(item);
        });

        attributeList = filteredAttributeList;

        this.createView('edit', 'views/admin/layouts/record/edit-attributes', {
            selector: '.edit-container',
            attributeList: attributeList,
            attributeDefs: this.options.attributeDefs,
            dynamicLogicDefs: this.options.dynamicLogicDefs,
            model: model,
        });
    }

    actionSave() {
        const editView = /** @type {import('views/record/edit').default} */this.getView('edit');

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
