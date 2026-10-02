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

import BaseFieldView from 'views/fields/base';

export default class extends BaseFieldView {

    readOnly = true

    templateContent = `
        <button class="btn btn-default disabled" data-action="testConnection"
        >{{translate 'Test Connection' scope='EmailAccount'}}</button>
    `

    url = 'EmailAccount/action/testConnection'

    setup() {
        super.setup();

        this.addActionHandler('testConnection', () => this.test());
    }

    fetch() {
        return {};
    }

    checkAvailability() {
        if (this.model.get('host')) {
            this.$el.find('button').removeClass('disabled').removeAttr('disabled');
        } else {
            this.$el.find('button').addClass('disabled').attr('disabled', 'disabled');
        }
    }

    afterRender() {
        this.checkAvailability();

        this.stopListening(this.model, 'change:host');

        this.listenTo(this.model, 'change:host', () => {
            this.checkAvailability();
        });
    }

    getData() {
        return {
            host: this.model.get('host'),
            port: this.model.get('port'),
            security: this.model.get('security'),
            username: this.model.get('username'),
            password: this.model.get('password') || null,
            id: this.model.id,
            emailAddress: this.model.get('emailAddress'),
            userId: this.model.get('assignedUserId'),
        };
    }

    test() {
        const data = this.getData();

        const $btn = this.$el.find('button');

        $btn.addClass('disabled');

        Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

        Espo.Ajax.postRequest(this.url, data)
            .then(() => {
                $btn.removeClass('disabled');

                Espo.Ui.success(this.translate('connectionIsOk', 'messages', 'EmailAccount'));
            })
            .catch(xhr => {
                let statusReason = xhr.getResponseHeader('X-Status-Reason') || '';
                statusReason = statusReason.replace(/ $/, '');
                statusReason = statusReason.replace(/,$/, '');

                let msg = this.translate('Error');

                if (parseInt(xhr.status) !== 200) {
                    msg += ' ' + xhr.status;
                }

                if (statusReason) {
                    msg += ': ' + statusReason;
                }

                Espo.Ui.error(msg, true);

                console.error(msg);

                xhr.errorIsHandled = true;

                $btn.removeClass('disabled');
            });
    }
}
