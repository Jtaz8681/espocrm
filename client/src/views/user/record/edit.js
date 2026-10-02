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

import EditRecordView from 'views/record/edit';
import UserDetailRecordView from 'views/user/record/detail';

class UserEditRecordView extends EditRecordView {

    /**
     * @name model
     * @type module:models/user
     * @memberOf UserEditRecordView#
     */

    setup() {
        super.setup();

        this.setupNonAdminFieldsAccess();

        if (this.model.id === this.getUser().id) {
            this.listenTo(this.model, 'after:save', () => {
                this.getUser().set(this.model.getClonedAttributes());
            });
        }

        this.hideField('sendAccessInfo');

        this.passwordInfoMessage = this.getPasswordSendingMessage();

        if (!this.passwordInfoMessage) {
            this.hideField('passwordInfo');
        }

        let passwordChanged = false;

        this.listenToOnce(this.model, 'change:password', () => {
            passwordChanged = true;

            if (this.model.isNew()) {
                this.controlSendAccessInfoFieldForNew();

                return;
            }

            this.controlSendAccessInfoField();
        });

        this.listenTo(this.model, 'change', (model) => {
            if (!this.model.isNew() && !passwordChanged) {
                return;
            }

            if (
                !model.hasChanged('emailAddress') &&
                !model.hasChanged('portalsIds')&&
                !model.hasChanged('password')
            ) {
                return;
            }

            if (this.model.isNew()) {
                this.controlSendAccessInfoFieldForNew();

                return;
            }

            this.controlSendAccessInfoField();
        });

        UserDetailRecordView.prototype.setupFieldAppearance.call(this);

        this.hideField('passwordPreview');

        this.listenTo(this.model, 'change:passwordPreview', (model, value) => {
            value = value || '';

            if (value.length) {
                this.showField('passwordPreview');
            } else {
                this.hideField('passwordPreview');
            }
        });


        this.listenTo(this.model, 'after:save', () => {
            this.model.unset('password', {silent: true});
            this.model.unset('passwordConfirm', {silent: true});
        });
    }

    controlSendAccessInfoField() {
        if (this.isPasswordSendable() && this.model.get('password')) {
            this.showField('sendAccessInfo');

            return;
        }

        this.hideField('sendAccessInfo');

        if (!this.model.has('sendAccessInfo')) {
            return;
        }

        this.model.set('sendAccessInfo', false);
    }

    controlSendAccessInfoFieldForNew() {
        const skipSettingTrue = this.recordHelper.getFieldStateParam('sendAccessInfo', 'hidden') === false;

        if (this.isPasswordSendable()) {
            this.showField('sendAccessInfo');

            if (!skipSettingTrue) {
                this.model.set('sendAccessInfo', true);
            }

            return;
        }

        this.hideField('sendAccessInfo');

        this.model.set('sendAccessInfo', false);
    }

    // noinspection SpellCheckingInspection
    isPasswordSendable() {
        if (this.model.isPortal()) {
            if (!(this.model.get('portalsIds') || []).length) {
                return false;
            }
        }

        if (!this.model.get('emailAddress')) {
            return false;
        }

        return true;
    }


    setupNonAdminFieldsAccess() {
        UserDetailRecordView.prototype.setupNonAdminFieldsAccess.call(this);
    }

    // noinspection JSUnusedGlobalSymbols
    controlFieldAppearance() {
        UserDetailRecordView.prototype.controlFieldAppearance.call(this);
    }

    getGridLayout(callback) {
        this.getHelper().layoutManager
            .get(this.model.entityType, this.options.layoutName || this.layoutName, simpleLayout => {
                /** @type {module:views/record/detail~panelDefs[]} */
                const layout = Espo.Utils.cloneDeep(simpleLayout);

                /** @type {module:views/record/detail~panelDefs[]} */
                const panels = [];

                panels.push({
                    "label": "Teams and Access Control",
                    "name": "accessControl",
                    "rows": [
                        [{"name": "type"}, {"name": "isActive"}],
                        [{"name": "teams"}, {"name": "defaultTeam"}],
                        [{"name": "roles"}, false]
                    ]
                });

                panels.push({
                    "label": "Portal",
                    "name": "portal",
                    "rows": [
                        [{"name": "portals"}, {"name": "accounts"}],
                        [{"name": "portalRoles"}, {"name": "contact"}]
                    ]
                });

                if (this.getUser().isAdmin() && this.model.isPortal()) {
                    panels.push({
                        "label": "Misc",
                        "name": "portalMisc",
                        "rows": [
                            [{"name": "dashboardTemplate"}, false]
                        ]
                    });
                }

                if (this.model.isAdmin() || this.model.isRegular()) {
                    panels.push({
                        "label": "Misc",
                        "name": "misc",
                        "rows": [
                            [{"name": "workingTimeCalendar"}, {"name": "layoutSet"}]
                        ]
                    });
                }

                if (
                    this.type === this.TYPE_EDIT &&
                    this.getUser().isAdmin() &&
                    !this.model.isApi()
                ) {
                    panels.push({
                        label: 'Password',
                        rows: [
                            [
                                {
                                    name: 'password',
                                    type: 'password',
                                    params: {
                                        required: false,
                                        readyToChange: true,
                                    },
                                    view: 'views/user/fields/password',
                                },
                                {
                                    name: 'generatePassword',
                                    view: 'views/user/fields/generate-password',
                                    customLabel: '',
                                },
                            ],
                            [
                                {
                                    name: 'passwordConfirm',
                                    type: 'password',
                                    params: {
                                        required: false,
                                        readyToChange: true
                                    }
                                },
                                {
                                    name: 'passwordPreview',
                                    view: 'views/fields/base',
                                    params: {
                                        readOnly: true
                                    },
                                },
                            ],
                            [
                                {
                                    name: 'sendAccessInfo'
                                },
                                {
                                    name: 'passwordInfo',
                                    type: 'text',
                                    customLabel: '',
                                    customCode: this.passwordInfoMessage,
                                },
                            ]
                        ]
                    });
                }

                if (this.getUser().isAdmin() && this.model.isApi()) {
                    panels.push({
                        "name": "auth",
                        "rows": [
                            [{"name": "authMethod"}, false]
                        ]
                    });
                }

                let hasTab = false;

                for (const [i, panel] of layout.entries()) {
                    if (panel.tabBreak && i > 0) {
                        layout.splice(i, 0, ...panels);

                        hasTab = true;

                        break;
                    }
                }

                if (!hasTab) {
                    layout.push(...panels);
                }

                this.detailLayout = layout;

                const gridLayout = {
                    type: 'record',
                    layout: this.convertDetailLayout(layout),
                };

                callback(gridLayout);
            });
    }

    getPasswordSendingMessage() {
        if (this.getConfig().get('outboundEmailFromAddress')) {
            return '';
        }

        let msg = this.translate('setupSmtpBefore', 'messages', 'User')
            .replace('{url}', '#Admin/outboundEmails');

        msg = this.getHelper().transformMarkdownInlineText(msg);

        return msg;
    }

    fetch() {
        const data = super.fetch();

        if (!this.isNew) {
            if (
                'password' in data &&
                (data['password'] === '' || data['password'] == null)
            ) {
                delete data['password'];
                delete data['passwordConfirm'];

                this.model.unset('password');
                this.model.unset('passwordConfirm');
            }
        }

        return data;
    }

    exit(after) {
        if (after === 'create' || after === 'save') {
            this.model.unset('sendAccessInfo', {silent: true});
        }

        super.exit(after);
    }

    // noinspection JSUnusedGlobalSymbols
    errorHandlerUserNameExists() {
        Espo.Ui.error(this.translate('userNameExists', 'messages', 'User'))
    }
}

export default UserEditRecordView;
