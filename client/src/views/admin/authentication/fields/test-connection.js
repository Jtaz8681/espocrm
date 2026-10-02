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

    // language=Handlebars
    templateContent = `
        <button
            class="btn btn-default"
            data-action="testConnection"
        >{{translate \'Test Connection\' scope=\'Settings\'}}</button>
    `

    fetch() {
        return {};
    }

    setup() {
        super.setup();

        this.addActionHandler('testConnection', () => this.testConnection());
    }


    getConnectionData() {
        return {
            'host': this.model.get('ldapHost'),
            'port': this.model.get('ldapPort'),
            'useSsl': this.model.get('ldapSecurity'),
            'useStartTls': this.model.get('ldapSecurity'),
            'username': this.model.get('ldapUsername'),
            'password': this.model.get('ldapPassword'),
            'bindRequiresDn': this.model.get('ldapBindRequiresDn'),
            'accountDomainName': this.model.get('ldapAccountDomainName'),
            'accountDomainNameShort': this.model.get('ldapAccountDomainNameShort'),
            'accountCanonicalForm': this.model.get('ldapAccountCanonicalForm'),
        };
    }

    testConnection() {
        const data = this.getConnectionData();

        this.$el.find('button').prop('disabled', true);

        Espo.Ui.notify(this.translate('Connecting', 'labels', 'Settings'));

        Espo.Ajax.postRequest('Ldap/action/testConnection', data)
            .then(() => {
                this.$el.find('button').prop('disabled', false);

                Espo.Ui.success(this.translate('ldapTestConnection', 'messages', 'Settings'));
            })
            .catch(xhr => {
                let statusReason = xhr.getResponseHeader('X-Status-Reason') || '';
                statusReason = statusReason.replace(/ $/, '');
                statusReason = statusReason.replace(/,$/, '');

                let msg = this.translate('Error') + ' ' + xhr.status;

                if (statusReason) {
                    msg += ': ' + statusReason;
                }

                Espo.Ui.error(msg, true);

                console.error(msg);

                xhr.errorIsHandled = true;

                this.$el.find('button').prop('disabled', false);
            });
    }
}
