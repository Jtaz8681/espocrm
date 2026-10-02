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

export default class IntegrationsIndexView extends View {

    template = 'admin/integrations/index'

    /**
     * @private
     * @type {string[]}
     */
    integrationList

    integration = null

    data() {
        return {
            integrationDataList: this.getIntegrationDataList(),
            integration: this.integration,
        };
    }

    setup () {
        this.addHandler('click', 'a.integration-link', (e, target) => {
            this.openIntegration(target.dataset.name);
        });

        this.integrationList = Object.keys(this.getMetadata().get('integrations') || {})
            .sort((v1, v2) => this.translate(v1, 'titles', 'Integration')
                .localeCompare(this.translate(v2, 'titles', 'Integration'))
            );

        this.integration = this.options.integration || null;

        if (this.integration) {
            this.createIntegrationView(this.integration);
        }

        this.on('after:render', () => {
            this.renderHeader();

            if (!this.integration) {
                this.renderDefaultPage();
            }
        });
    }

    /**
     * @return {{name: string, active: boolean}[]}
     */
    getIntegrationDataList() {
        return this.integrationList.map(it => {
            return {
                name: it,
                active: this.integration === it,
            };
        })
    }

    /**
     * @param {string} integration
     * @return {Promise<Bull.View>}
     */
    createIntegrationView(integration) {
        const viewName = this.getMetadata().get(`integrations.${integration}.view`) ||
            'views/admin/integrations/' +
            Espo.Utils.camelCaseToHyphen(this.getMetadata().get(`integrations.${integration}.authMethod`));

        return this.createView('content', viewName, {
            fullSelector: '#integration-content',
            integration: integration,
        });
    }

    /**
     * @param {string} integration
     */
    async openIntegration(integration) {
        this.integration = integration;

        this.getRouter().navigate(`#Admin/integrations/name=${integration}`, {trigger: false});

        Espo.Ui.notifyWait();

        await this.createIntegrationView(integration);

        this.renderHeader();
        await this.reRender();

        Espo.Ui.notify(false);
        $(window).scrollTop(0);
    }

    afterRender() {
        this.$header = $('#integration-header');
    }

    renderDefaultPage() {
        this.$header.html('').hide();

        let msg;

        if (this.integrationList.length) {
            msg = this.translate('selectIntegration', 'messages', 'Integration');
        } else {
            msg = '<p class="lead">' + this.translate('noIntegrations', 'messages', 'Integration') + '</p>';
        }

        $('#integration-content').html(msg);
    }

    renderHeader() {
        if (!this.integration) {
            this.$header.html('');

            return;
        }

        this.$header.show().html(this.translate(this.integration, 'titles', 'Integration'));
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Integrations', 'labels', 'Admin'));
    }
}
