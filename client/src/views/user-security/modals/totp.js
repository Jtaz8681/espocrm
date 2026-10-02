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

export default class TotpModalView extends ModalView {

    template = 'user-security/modals/totp'

    className = 'dialog dialog-record'

    shortcutKeys = {
        'Control+Enter': 'apply'
    }

    setup() {
        this.buttonList = [
            {
                name: 'apply',
                label: 'Apply',
                style: 'danger',
                onClick: () => this.actionApply(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.headerHtml = '&nbsp';

        const model = new Model();

        model.name = 'UserSecurity';

        this.wait(
            Espo.Ajax
                .postRequest('UserSecurity/action/getTwoFactorUserSetupData', {
                    id: this.model.id,
                    password: this.model.get('password'),
                    auth2FAMethod: this.model.get('auth2FAMethod'),
                    reset: this.options.reset,
                })
                .then(/** Record */data => {
                    this.label = data.label;
                    this.secret = data.auth2FATotpSecret;

                    model.set('secret', data.auth2FATotpSecret);
                })
        );

        model.setDefs({
            fields: {
                code: {
                    type: 'varchar',
                    required: true,
                    maxLength: 7,
                },
                secret: {
                    type: 'varchar',
                    readOnly: true,
                },
            }
        });

        this.createView('record', 'views/record/edit-for-modal', {
            scope: 'None',
            selector: '.record',
            model: model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                name: 'secret',
                                labelText: this.translate('Secret', 'labels', 'User'),
                            },
                            false
                        ],
                        [
                            {
                                name: 'code',
                                labelText: this.translate('Code', 'labels', 'User'),
                            },
                            false
                        ]
                    ]
                }
            ],
        });

        Espo.loader.requirePromise('lib!qrcodejs').then(lib => {
            QRCode = lib;
        })
    }

    afterRender() {
        new QRCode(this.$el.find('.qrcode').get(0), {
            text: `otpauth://totp/${this.label}?secret=${this.secret}`,
            width: 256,
            height: 256,
            colorDark : '#000000',
            colorLight : '#ffffff',
            correctLevel : QRCode.CorrectLevel.H,
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
