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

define('crm:views/mass-email/record/edit', ['views/record/edit'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.initFieldsControl();
        },

        initFieldsControl: function () {
            this.listenTo(this.model, 'change:smtpAccount', (model, value, o) => {
                if (!o.ui) {
                    return;
                }

                if (!value || value === 'system') {
                    this.model.set('fromAddress', this.getConfig().get('outboundEmailFromAddress') || '');
                    this.model.set('fromName', this.getConfig().get('outboundEmailFromName') || '');

                    return;
                }

                var smtpAccountView = this.getFieldView('smtpAccount');

                if (!smtpAccountView) {
                    return;
                }

                if (!smtpAccountView.loadedOptionAddresses) {
                    return;
                }

                if (!smtpAccountView.loadedOptionAddresses[value]) {
                    return;
                }

                this.model.set('fromAddress', smtpAccountView.loadedOptionAddresses[value]);
                this.model.set('fromName', smtpAccountView.loadedOptionFromNames[value]);
            });
        },
    });
});
