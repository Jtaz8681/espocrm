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

class SettingsEditTabUrlModalView extends Modal {

    className = 'dialog dialog-record'

    templateContent = `<div class="record no-side-margin">{{{record}}}</div>`

    setup() {
        super.setup();

        this.headerText = this.translate('URL', 'labels', 'Settings');

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
                            name: 'url',
                            labelText: this.translate('URL', 'labels', 'Settings'),
                            view: 'views/settings/fields/tab-url',
                        }
                    ],
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
                        }
                    ],
                    [
                        {
                            name: 'aclScope',
                            labelText: this.translate('aclScope', 'fields', 'Admin'),
                        },
                        {
                            name: 'onlyAdmin',
                            labelText: this.translate('onlyAdmin', 'fields', 'Admin'),
                        },
                        {
                            name: 'openInNewTab',
                            labelText: this.translate('openInNewTab', 'fields', 'Admin'),
                        },
                    ]
                ]
            }
        ];

        const model = this.model = new Model();

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
                url: {
                    type: 'url',
                    required: true,
                    tooltip: 'Admin.tabUrl',
                },
                aclScope: {
                    type: 'enum',
                    translation: 'Global.scopeNames',
                    options: ['', ...this.getAclScopes()],
                    tooltip: 'Admin.tabUrlAclScope',
                },
                onlyAdmin: {
                    type: 'bool',
                },
                openInNewTab: {
                    type: 'bool',
                },
            },
        });

        this.createView('record', 'views/record/edit-for-modal', {
            detailLayout: detailLayout,
            model: model,
            selector: '.record',
        }).then(/** import('views/record/edit').default */view => {
            if (this.options.parentType === 'Preferences') {
                view.hideField('aclScope');
                view.hideField('onlyAdmin');
            }
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

    /**
     * @return {string[]}
     */
    getAclScopes() {
        return this.getMetadata().getScopeList()
            .filter(scope => {
                return this.getMetadata().get(`scopes.${scope}.acl`);
            });
    }
}

// noinspection JSUnusedGlobalSymbols
export default SettingsEditTabUrlModalView;
