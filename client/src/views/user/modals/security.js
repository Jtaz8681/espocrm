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

class UserSecurityModalView extends ModalView {

    templateContent = '<div class="record no-side-margin">{{{record}}}</div>'

    className = 'dialog dialog-record'

    shortcutKeys = {
        'Control+Enter': 'apply',
    }

    setup() {
        this.buttonList = [
            {
                name: 'apply',
                label: 'Apply',
                hidden: true,
                style: 'danger',
                onClick: () => this.apply(),
            },
            {
                name: 'cancel',
                label: 'Close',
            }
        ];

        this.dropdownItemList = [
            {
                name: 'reset',
                text: this.translate('Reset 2FA'),
                hidden: true,
                onClick: () => this.reset(),
            },
        ];

        this.userModel = this.options.userModel;

        this.$header = $('<span>').append(
            $('<span>').text(this.translate('Security')),
            ' <span class="chevron-right"></span> ',
            $('<span>').text(this.userModel.get('userName'))
        );

        const model = this.model = new Model();

        model.name = 'UserSecurity';
        model.id = this.userModel.id;
        model.url = 'UserSecurity/' + this.userModel.id;

        const auth2FAMethodList = this.getConfig().get('auth2FAMethodList') || [];

        model.setDefs({
            fields: {
                'auth2FA': {
                    type: 'bool',
                    labelText: this.translate('auth2FAEnable', 'fields', 'User'),
                },
                'auth2FAMethod': {
                    type: 'enum',
                    options: auth2FAMethodList,
                    translation: 'Settings.options.auth2FAMethodList',
                },
            }
        });

        this.wait(
            model.fetch().then(() => {
                this.initialAttributes = Espo.Utils.cloneDeep(model.attributes);

                if (model.get('auth2FA')) {
                    this.showActionItem('reset');
                }

                this.createView('record', 'views/record/edit-for-modal', {
                    scope: 'None',
                    selector: '.record',
                    model: this.model,
                    detailLayout: [
                        {
                            rows: [
                                [
                                    {
                                        name: 'auth2FA',
                                        labelText: this.translate('auth2FAEnable', 'fields', 'User'),
                                    },
                                    {
                                        name: 'auth2FAMethod',
                                        labelText: this.translate('auth2FAMethod', 'fields', 'User'),
                                    }
                                ],
                            ]
                        }
                    ],
                }, (view) => {
                    this.controlFieldsVisibility(view);

                    this.listenTo(this.model, 'change:auth2FA', () => {
                        this.controlFieldsVisibility(view);
                    });
                });
            })
        );

        this.listenTo(this.model, 'change', () => {
            if (this.initialAttributes) {
                this.isChanged() ?
                    this.showActionItem('apply') :
                    this.hideActionItem('apply');
            }
        });
    }

    controlFieldsVisibility(view) {
        if (this.model.get('auth2FA')) {
            view.showField('auth2FAMethod');
            view.setFieldRequired('auth2FAMethod');
        }
        else {
            view.hideField('auth2FAMethod');
            view.setFieldNotRequired('auth2FAMethod');
        }
    }

    isChanged() {
        return this.initialAttributes.auth2FA !== this.model.get('auth2FA') ||
            this.initialAttributes.auth2FAMethod !== this.model.get('auth2FAMethod')
    }

    reset() {
        this.confirm(this.translate('security2FaResetConfirmation', 'messages', 'User'), () => {
            this.apply(true);
        });
    }

    /**
     * @return {module:views/record/edit}
     */
    getRecordView() {
        return this.getView('record');
    }

    apply(reset) {
        const data = this.getRecordView().processFetch();

        if (!data) {
            return;
        }

        this.hideActionItem('apply');

        new Promise(resolve => {
            this.createView('dialog', 'views/user/modals/password', {}, passwordView => {
                passwordView.render();

                this.listenToOnce(passwordView, 'cancel', () => this.showActionItem('apply'));

                this.listenToOnce(passwordView, 'proceed', (data) => {
                    this.model.set('password', data.password);

                    passwordView.close();

                    resolve();
                });
            });
        }).then(() => this.processApply(reset));
    }

    processApply(reset) {
        if (this.model.get('auth2FA')) {
            const auth2FAMethod = this.model.get('auth2FAMethod');

            const view = this.getMetadata().get(['app', 'authentication2FAMethods', auth2FAMethod, 'userApplyView']);

            if (view) {
                Espo.Ui.notifyWait();

                this.createView('dialog', view, {
                    model: this.model,
                    reset: reset,
                }, view => {
                    Espo.Ui.notify(false);

                    view.render();

                    this.listenToOnce(view, 'cancel', () => {
                        this.close();
                    });

                    this.listenToOnce(view, 'apply', () => {
                        view.close();

                        this.processSave();
                    });

                    this.listenToOnce(view, 'done', () => {
                        Espo.Ui.success(this.translate('Done'));
                        this.trigger('done');

                        view.close();
                        this.close();
                    });
                });

                return ;
            }

            if (reset) {
                this.model.set('auth2FA', false);
            }

            this.processSave();

            return;
        }

        this.processSave();
    }

    processSave() {
        this.hideActionItem('apply');

        this.model
            .save()
            .then(() => {
                this.close();

                Espo.Ui.success(this.translate('Done'));
            })
            .catch(() => this.showActionItem('apply'));
    }
}

export default UserSecurityModalView;
