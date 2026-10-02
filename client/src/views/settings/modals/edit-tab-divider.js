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

class EditTabDividerSettingsModalView extends ModalView {

    className = 'dialog dialog-record'

    templateContent = '<div class="record no-side-margin">{{{record}}}</div>'

    setup() {
        super.setup();

        this.headerText = this.translate('Divider', 'labels', 'Settings');

        this.buttonList.push({
            name: 'apply',
            label: 'Apply',
            style: 'danger',
        });

        this.buttonList.push({
            name: 'cancel',
            label: 'Cancel',
        });

        this.shortcutKeys = {
            'Control+Enter': () => this.actionApply(),
        };

        let detailLayout = [
            {
                rows: [
                    [
                        {
                            name: 'text',
                            labelText: this.options.parentType === 'Preferences' ?
                                this.translate('label', 'tabFields', 'Preferences') :
                                this.translate('label', 'fields', 'Admin'),
                        },
                        false,
                    ],
                ]
            }
        ];

        let model = this.model = new Model({}, {entityType: 'Dummy'});

        model.set(this.options.itemData);
        model.setDefs({
            fields: {
                text: {
                    type: 'varchar',
                },
            },
        });

        this.createView('record', 'views/record/edit-for-modal', {
            detailLayout: detailLayout,
            model: model,
            selector: '.record',
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionApply() {
        let recordView = /** @type {module:views/record/edit}*/ this.getView('record');

        if (recordView.validate()) {
            return;
        }

        let data = recordView.fetch();

        this.trigger('apply', data);
    }
}

// noinspection JSUnusedGlobalSymbols
export default EditTabDividerSettingsModalView;
