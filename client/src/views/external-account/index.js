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

import View from 'view';

class ExternalAccountIndex extends View {

    template = 'external-account/index'

    data() {
        return {
            externalAccountList: this.externalAccountList,
            id: this.id,
            externalAccountListCount: this.externalAccountList.length
        };
    }

    setup() {
        this.addHandler('click', '#external-account-menu a.external-account-link', (e, target) => {
            const id = `${target.dataset.id}__${this.userId}`;

            this.openExternalAccount(id);
        });

        this.externalAccountList = this.collection.models.map(model => model.getClonedAttributes());

        this.userId = this.getUser().id;
        this.id = this.options.id || null;

        if (this.id) {
            this.userId = this.id.split('__')[1];
        }

        this.on('after:render', () => {
            this.renderHeader();

            if (!this.id) {
                this.renderDefaultPage();
            } else {
                this.openExternalAccount(this.id);
            }
        });
    }

    openExternalAccount(id) {
        this.id = id;

        const integration = this.integration = id.split('__')[0];

        this.userId = id.split('__')[1];

        this.getRouter().navigate(`#ExternalAccount/edit/${id}`, {trigger: false});

        const authMethod = this.getMetadata().get(['integrations', integration, 'authMethod']);

        const viewName =
            this.getMetadata().get(['integrations', integration, 'userView']) ||
            'views/external-account/' + Espo.Utils.camelCaseToHyphen(authMethod);

        Espo.Ui.notifyWait();

        this.createView('content', viewName, {
            fullSelector: '#external-account-content',
            id: id,
            integration: integration
        }, view => {
            this.renderHeader();
            view.render();
            Espo.Ui.notify(false);

            $(window).scrollTop(0);

            this.controlCurrentLink(id);
        });
    }

    controlCurrentLink() {
        const id = this.integration;

        this.element.querySelectorAll('.external-account-link').forEach(element => {
            element.classList.remove('disabled', 'text-muted');
        });

        const currentLink = this.element.querySelector(`.external-account-link[data-id="${id}"]`);

        if (currentLink) {
            currentLink.classList.add('disabled', 'text-muted');
        }
    }

    renderDefaultPage() {
        $('#external-account-header').html('').hide();
        $('#external-account-content').html('');
    }

    renderHeader() {
        const $header = $('#external-account-header');

        if (!this.id) {
            $header.html('');

            return;
        }

        $header.show().text(this.integration);
    }

    updatePageTitle() {
        this.setPageTitle(this.translate('ExternalAccount', 'scopeNamesPlural'));
    }
}

export default ExternalAccountIndex;
