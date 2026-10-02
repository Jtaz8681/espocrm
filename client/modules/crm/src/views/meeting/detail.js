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
import moment from 'moment';

class MeetingDetailView extends DetailView {

    cancellationPeriod = '8 hours'

    setup() {
        super.setup();

        this.setupStatuses();

        this.addMenuItem('buttons', {
            name: 'sendInvitations',
            text: this.translate('Send Invitations', 'labels', 'Meeting'),
            acl: 'edit',
            hidden: true,
            onClick: () => this.actionSendInvitations(),
        });

        this.addMenuItem('dropdown', {
            name: 'sendCancellation',
            text: this.translate('Send Cancellation', 'labels', 'Meeting'),
            acl: 'edit',
            hidden: true,
            onClick: () => this.actionSendCancellation(),
        });

        this.addMenuItem('buttons', {
            name: 'setAcceptanceStatus',
            text: '',
            style: 'text',
            hidden: true,
            onClick: () => this.actionSetAcceptanceStatus(),
        });

        this.setupCancellationPeriod();

        this.controlSendInvitationsButton();
        this.controlAcceptanceStatusButton();
        this.controlSendCancellationButton();

        this.listenTo(this.model, 'sync', () => {
            this.controlSendInvitationsButton();
            this.controlSendCancellationButton();
        });

        this.listenTo(this.model, 'sync', () => this.controlAcceptanceStatusButton());
    }

    setupStatuses() {
        this.canceledStatusList = this.getMetadata().get(`scopes.${this.entityType}.canceledStatusList`) || [];

        this.notActualStatusList = [
            ...(this.getMetadata().get(`scopes.${this.entityType}.completedStatusList`) || []),
            ...this.canceledStatusList,
        ];
    }

    setupCancellationPeriod() {
        this.cancellationPeriodAmount = 0;
        this.cancellationPeriodUnits = 'hours';

        const cancellationPeriod = this.getConfig().get('eventCancellationPeriod') || this.cancellationPeriod;

        if (!cancellationPeriod) {
            return;
        }

        const arr = cancellationPeriod.split(' ');

        this.cancellationPeriodAmount = parseInt(arr[0]);
        this.cancellationPeriodUnits = arr[1] ?? 'hours';
    }

    controlAcceptanceStatusButton() {
        if (!this.model.has('status')) {
            return;
        }

        if (!this.model.has('usersIds')) {
            return;
        }

        if (this.notActualStatusList.includes(this.model.get('status'))) {
            this.hideHeaderActionItem('setAcceptanceStatus');

            return;
        }

        if (!this.model.getLinkMultipleIdList('users').includes(this.getUser().id)) {
            this.hideHeaderActionItem('setAcceptanceStatus');

            return;
        }

        const acceptanceStatus = this.model.getLinkMultipleColumn('users', 'status', this.getUser().id);

        let text;
        let style = 'default';

        if (acceptanceStatus && acceptanceStatus !== 'None') {
            text = this.getLanguage().translateOption(acceptanceStatus, 'acceptanceStatus', this.model.entityType);

            style = this.getMetadata()
                .get(['entityDefs', this.model.entityType, 'fields',
                    'acceptanceStatus', 'style', acceptanceStatus]);
        }
        else {
            text = this.translate('Acceptance', 'labels', 'Meeting');
        }

        let iconHtml = '';

        if (style) {
            const iconClass = ({
                'success': 'fas fa-check-circle',
                'danger': 'fas fa-times-circle',
                'warning': 'fas fa-question-circle',
            })[style];

            iconHtml = $('<span>')
                .addClass(iconClass)
                .addClass('text-' + style)
                .get(0).outerHTML;
        }

        this.updateMenuItem('setAcceptanceStatus', {
            text: text,
            iconHtml: iconHtml,
            hidden: false,
        });
    }

    controlSendInvitationsButton() {
        let show = true;

        if (this.notActualStatusList.includes(this.model.get('status'))) {
            show = false;
        }

        if (
            show &&
            !this.getAcl().checkModel(this.model, 'edit')
        ) {
            show = false;
        }

        if (show) {
            const userIdList = this.model.getLinkMultipleIdList('users');
            const contactIdList = this.model.getLinkMultipleIdList('contacts');
            const leadIdList = this.model.getLinkMultipleIdList('leads');

            if (!contactIdList.length && !leadIdList.length && !userIdList.length) {
                show = false;
            }
            /*else if (
                !contactIdList.length &&
                !leadIdList.length &&
                userIdList.length === 1 &&
                userIdList[0] === this.getUser().id &&
                this.model.getLinkMultipleColumn('users', 'status', this.getUser().id) === 'Accepted'
            ) {
                show = false;
            }*/
        }

        if (show) {
            const dateEnd = this.model.get('dateEnd');

            if (
                dateEnd &&
                this.getDateTime().toMoment(dateEnd).isBefore(moment.now())
            ) {
                show = false;
            }
        }

        show ?
            this.showHeaderActionItem('sendInvitations') :
            this.hideHeaderActionItem('sendInvitations');
    }

    controlSendCancellationButton() {
        let show = this.canceledStatusList.includes(this.model.get('status'));

        if (show) {
            const dateEnd = this.model.get('dateEnd');

            if (
                dateEnd &&
                this.getDateTime()
                    .toMoment(dateEnd)
                    .add(this.cancellationPeriodAmount, this.cancellationPeriodUnits)
                    .isBefore(moment.now())
            ) {
                show = false;
            }
        }

        if (show) {
            const userIdList = this.model.getLinkMultipleIdList('users');
            const contactIdList = this.model.getLinkMultipleIdList('contacts');
            const leadIdList = this.model.getLinkMultipleIdList('leads');

            if (!contactIdList.length && !leadIdList.length && !userIdList.length) {
                show = false;
            }
        }

        show ?
            this.showHeaderActionItem('sendCancellation') :
            this.hideHeaderActionItem('sendCancellation');
    }

    actionSendInvitations() {
        Espo.Ui.notifyWait();

        this.createView('dialog', 'crm:views/meeting/modals/send-invitations', {
            model: this.model,
        }).then(view => {
            Espo.Ui.notify(false);
            view.render();

            this.listenToOnce(view, 'sent', () => this.model.fetch());
        });
    }

    actionSendCancellation() {
        Espo.Ui.notifyWait();

        this.createView('dialog', 'crm:views/meeting/modals/send-cancellation', {
            model: this.model,
        }).then(view => {
            Espo.Ui.notify(false);
            view.render();

            this.listenToOnce(view, 'sent', () => this.model.fetch());
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionSetAcceptanceStatus() {

        this.createView('dialog', 'crm:views/meeting/modals/acceptance-status', {
            model: this.model
        }, (view) => {
            view.render();


            this.listenTo(view, 'set-status', (status) => {
                this.disableMenuItem('setAcceptanceStatus');
                Espo.Ui.notifyWait();

                Espo.Ajax
                    .postRequest(this.model.entityType + '/action/setAcceptanceStatus', {
                        id: this.model.id,
                        status: status,
                    })
                    .then(() => {
                        this.model.fetch()
                            .then(() => {
                                Espo.Ui.notify(false);
                                this.enableMenuItem('setAcceptanceStatus');
                            });
                    })
                    .catch(() => this.enableMenuItem('setAcceptanceStatus'));
            });
        });
    }
}

export default MeetingDetailView;
