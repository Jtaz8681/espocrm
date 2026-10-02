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

export default class extends ModalView {

    template = 'personal-data/modals/personal-data'

    className = 'dialog dialog-record'
    backdrop = true

    setup() {
        super.setup();

        this.buttonList = [
            {
                name: 'cancel',
                label: 'Close'
            },
        ];

        this.headerText = this.getLanguage().translate('Personal Data');
        this.headerText += ': ' + this.model.get('name');

        if (this.getAcl().check(this.model, 'edit')) {
            this.buttonList.unshift({
                name: 'erase',
                label: 'Erase',
                style: 'danger',
                disabled: true,
                onClick: () => this.actionErase(),
            });
        }

        this.fieldList = [];

        this.scope = this.model.entityType;

        this.createView('record', 'views/personal-data/record/record', {
            selector: '.record',
            model: this.model,
        }, (view) => {
            this.listenTo(view, 'check', (fieldList) => {
                this.fieldList = fieldList;

                if (fieldList.length) {
                    this.enableButton('erase');
                } else {
                    this.disableButton('erase');
                }
            });

            if (!view.fieldList.length) {
                this.disableButton('export');
            }
        });
    }

    actionErase() {
        this.confirm({
            message: this.translate('erasePersonalDataConfirmation', 'messages'),
            confirmText: this.translate('Erase')
        }, () => {
            this.disableButton('erase');

            Espo.Ajax.postRequest('DataPrivacy/action/erase', {
                fieldList: this.fieldList,
                entityType: this.scope,
                id: this.model.id,
            }).then(() => {
                Espo.Ui.success(this.translate('Done'));

                this.trigger('erase');
            })
            .catch(() => {
                this.enableButton('erase');
            });
        });
    }
}
