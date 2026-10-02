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

import Modal from 'views/modal';
import Model from 'model';

class SettingsEditTabGroupModalView extends Modal {

    className = 'dialog dialog-record'

    templateContent = `<div class="record no-side-margin">{{{record}}}</div>`

    setup() {
        super.setup();

        this.headerText = this.translate('Group Tab', 'labels', 'Settings');

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

        const detailLayout = [
            {
                rows: [
                    [
                        {
                            name: 'text',
                            labelText: this.options.parentType === 'Preferences' ?
                                this.translate('label', 'tabFields', 'Preferences') :
                                this.translate('label', 'fields', 'Admin'),
                        },
                        {
                            name: 'iconClass',
                            labelText: this.options.parentType === 'Preferences' ?
                                this.translate('iconClass', 'tabFields', 'Preferences') :
                                this.translate('iconClass', 'fields', 'EntityManager'),
                        },
                        {
                            name: 'color',
                            labelText: this.options.parentType === 'Preferences' ?
                                this.translate('color', 'tabFields', 'Preferences') :
                                this.translate('color', 'fields', 'EntityManager'),
                        },
                    ],
                    [
                        {
                            name: 'itemList',
                            labelText:this.options.parentType === 'Preferences' ?
                                this.translate('tabList', 'fields', 'Preferences') :
                                this.translate('tabList', 'fields', 'Settings'),
                        },
                        false
                    ]
                ]
            }
        ];

        const model = this.model = new Model();

        model.name = 'GroupTab';
        model.set(this.options.itemData);

        model.setDefs({
            fields: {
                text: {
                    type: 'varchar',
                },
                iconClass: {
                    type: 'base',
                    view: 'views/admin/entity-manager/fields/icon-class',
                },
                color: {
                    type: 'base',
                    view: 'views/fields/colorpicker',
                },
                itemList: {
                    type: 'array',
                    view: 'views/settings/fields/group-tab-list',
                },
            },
        });

        this.createView('record', 'views/record/edit-for-modal', {
            detailLayout: detailLayout,
            model: model,
            selector: '.record',
        });
    }

    actionApply() {
        const recordView = /** @type {import('views/record/edit').default} */this.getView('record');

        if (recordView.validate()) {
            return;
        }

        const data = recordView.fetch();

        this.trigger('apply', data);
    }
}

export default SettingsEditTabGroupModalView;
