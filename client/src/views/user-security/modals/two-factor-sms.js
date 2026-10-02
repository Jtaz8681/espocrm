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

// noinspection JSUnusedGlobalSymbols
export default class TwoFactorSmsModalView extends ModalView {

    template = 'user-security/modals/two-factor-sms'

    className = 'dialog dialog-record'

    shortcutKeys = {
        'Control+Enter': 'apply',
    }

    setup() {
        this.addActionHandler('sendCode', () => this.actionSendCode());

        this.buttonList = [
            {
                name: 'apply',
                label: 'Apply',
                style: 'danger',
                hidden: true,
                onClick: () => this.actionApply(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.headerHtml = '&nbsp';

        const codeLength = this.getConfig().get('auth2FASmsCodeLength') || 7;

        const model = new Model();

        model.name = 'UserSecurity';

        model.set('phoneNumber', null);

        model.setDefs({
            fields: {
                'code': {
                    type: 'varchar',
                    required: true,
                    maxLength: codeLength,
                },
                'phoneNumber': {
                    type: 'enum',
                    required: true,
                },
            }
        });

        this.internalModel = model;

        this.wait(
            Espo.Ajax
                .postRequest('UserSecurity/action/getTwoFactorUserSetupData', {
                    id: this.model.id,
                    password: this.model.get('password'),
                    auth2FAMethod: this.model.get('auth2FAMethod'),
                    reset: this.options.reset,
                })
                .then(data => {
                    this.phoneNumberList = data.phoneNumberList;

                    this.createView('record', 'views/record/edit-for-modal', {
                        scope: 'None',
                        selector: '.record',
                        model: model,
                        detailLayout: [
                            {
                                rows: [
                                    [
                                        {
                                            name: 'phoneNumber',
                                            labelText: this.translate('phoneNumber', 'fields', 'User'),
                                        },
                                        false
                                    ],
                                    [
                                        {
                                            name: 'code',
                                            labelText: this.translate('Code', 'labels', 'User'),
                                        },
                                        false
                                    ],
                                ]
                            }
                        ],
                    }, view => {
                        view.setFieldOptionList('phoneNumber', this.phoneNumberList);

                        if (this.phoneNumberList.length) {
                            model.set('phoneNumber', this.phoneNumberList[0]);
                        }

                        view.hideField('code');
                    });
                })
        );
    }

    afterRender() {
        this.$sendCode = this.$el.find('[data-action="sendCode"]');

        this.$pInfo = this.$el.find('p.p-info');
        this.$pButton = this.$el.find('p.p-button');
        this.$pInfoAfter = this.$el.find('p.p-info-after');
    }

    actionSendCode() {
        this.$sendCode.attr('disabled', 'disabled').addClass('disabled');

        Espo.Ajax
            .postRequest('TwoFactorSms/action/sendCode', {
                id: this.model.id,
                phoneNumber: this.internalModel.get('phoneNumber'),
            })
            .then(() => {
                this.showActionItem('apply');

                this.$pInfo.addClass('hidden');
                this.$pButton.addClass('hidden');
                this.$pInfoAfter.removeClass('hidden');

                this.getRecordView().setFieldReadOnly('phoneNumber');
                this.getRecordView().showField('code');
            })
            .catch(() => {
                this.$sendCode.removeAttr('disabled').removeClass('disabled');
            });
    }

    /**
     * @return {import('views/record/edit').default}
     */
    getRecordView() {
        return this.getView('record');
    }

    actionApply() {
        const data = this.getRecordView().processFetch();

        if (!data) {
            return;
        }

        this.model.set('code', data.code);

        this.hideActionItem('apply');
        this.hideActionItem('cancel');

        Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

        this.model.save()
            .then(() => {
                Espo.Ui.notify(false);

                this.trigger('done');
            })
            .catch(() => {
                this.showActionItem('apply');
                this.showActionItem('cancel');
            });
    }
}
