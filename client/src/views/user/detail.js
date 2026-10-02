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

import DetailView from 'views/detail';

export default class extends DetailView {

    setup() {
        super.setup();

        if (this.getUser().isPortal()) {
            this.rootLinkDisabled = true;
        }

        if (this.model.id === this.getUser().id || this.getUser().isAdmin()) {
            if (
                this.getUserModel().isRegular() ||
                this.getUserModel().isAdmin() ||
                this.getUserModel().isPortal()
            ) {
                this.addMenuItem('dropdown', {
                    name: 'preferences',
                    label: 'Preferences',
                    action: 'preferences',
                    link: `#Preferences/edit/${this.model.id}`,
                    onClick: () => this.actionPreferences(),
                });
            }

            if (this.getUserModel().isRegular() || this.getUserModel().isAdmin()) {
                if (
                    (this.getAcl().check('EmailAccountScope') && this.model.id === this.getUser().id) ||
                    this.getUser().isAdmin()
                ) {
                    this.addMenuItem('dropdown', {
                        name: 'emailAccounts',
                        label: "Email Accounts",
                        action: 'emailAccounts',
                        link: `#EmailAccount/list/userId=${this.model.id}` +
                            `&userName=${encodeURIComponent(this.model.attributes.name)}`,
                        onClick: () => this.actionEmailAccounts(),
                    });
                }

                if (this.model.id === this.getUser().id && this.getAcl().checkScope('ExternalAccount')) {
                    this.addMenuItem('buttons', {
                        name: 'externalAccounts',
                        label: 'External Accounts',
                        action: 'externalAccounts',
                        link: '#ExternalAccount',
                        onClick: () => this.actionExternalAccounts(),
                    });
                }
            }
        }

        if (
            this.getAcl().checkScope('Calendar') &&
            (this.getUserModel().isRegular() || this.getUserModel().isAdmin())
        ) {
            const showActivities = this.getAcl().checkPermission('userCalendar', this.getUserModel());

            if (
                !showActivities &&
                this.getAcl().getPermissionLevel('userCalendar') === 'team' &&
                !this.model.has('teamsIds')
            ) {
                this.listenToOnce(this.model, 'sync', () => {
                    if (this.getAcl().checkPermission('userCalendar', this.getUserModel())) {
                        this.showHeaderActionItem('calendar');
                    }
                });
            }

            this.addMenuItem('buttons', {
                name: 'calendar',
                iconHtml: '<span class="far fa-calendar-alt"></span>',
                text: this.translate('Calendar', 'scopeNames'),
                link: `#Calendar/show/userId=${this.model.id}` +
                    `&userName=${encodeURIComponent(this.model.attributes.name)}`,
                hidden: !showActivities,
            })
        }
    }

    /**
     * @type {import('models/user').default}
     */
    getUserModel() {
        return /** @type {import('models/user').default} */this.model;
    }

    actionPreferences() {
        this.getRouter().navigate(`#Preferences/edit/${this.model.id}`, {trigger: true});
    }

    actionEmailAccounts() {
        this.getRouter().navigate(
            `#EmailAccount/list/userId=${this.model.id}&userName=${encodeURIComponent(this.model.attributes.name)}`,
            {trigger: true}
        );
    }

    actionExternalAccounts() {
        this.getRouter().navigate('#ExternalAccount', {trigger: true});
    }
}
