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
import VarcharFieldView from 'views/fields/varchar';

class SaveFiltersModalView extends ModalView {


    // language=Handlebars
    templateContent = `
        <div class="panel panel-default no-side-margin">
            <div class="panel-body">
                <div class="cell form-group" data-name="name">
                    <label
                        class="control-label"
                        data-name="name"
                    >{{translate 'name' category='fields'}}</label>
                    <div class="field" data-name="name">{{{nameField}}}</div>
                </div>
            </div>
        </div>
    `

    cssName = 'save-filters'

    /**
     * @private
     * @type {VarcharFieldView}
     */
    nameFieldView

    data() {
        return {
            dashletList: this.dashletList,
        };
    }

    setup() {
        this.shortcutKeys = {
            'Control+Enter': (e) => {
                e.preventDefault();
                e.stopPropagation();

                this.actionSave();
            },
        };

        this.buttonList = [
            {
                name: 'save',
                label: 'Save',
                style: 'primary',
                onClick: () => this.actionSave(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
                onClick: () => this.actionCancel(),
            },
        ];

        this.headerText = this.translate('Save Filter');

        this.formModel = new Model();

        this.nameFieldView = new VarcharFieldView({
            name: 'name',
            params: {
                required: true
            },
            mode: 'edit',
            model: this.formModel,
            labelText: this.translate('name', 'fields'),
        })

        this.assignView('nameField', this.nameFieldView);
    }

    afterRender() {
        setTimeout(() => {
            this.nameFieldView.element.querySelector('input')?.focus();
        }, 1);
    }

    actionSave() {
        this.fetchToModel();

        if (this.nameFieldView.validate()) {
            return;
        }

        this.trigger('save', this.formModel.attributes.name);

        return true;
    }

    /**
     * @private
     */
    fetchToModel() {
        this.formModel.setMultiple({
            ...this.nameFieldView.fetch(),
        });
    }
}

export default SaveFiltersModalView;
