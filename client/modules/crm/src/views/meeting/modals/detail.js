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

import moment from 'moment';
import DetailModalView from 'views/modals/detail';

class MeetingModalDetailView extends DetailModalView {

    duplicateAction = true

    setup() {
        super.setup();

        this.setupStatuses();
    }

    setupStatuses() {
        if (this.notActualStatusList) {
            return;
        }

        this.notActualStatusList = [
            ...(this.getMetadata().get(`scopes.${this.entityType}.completedStatusList`) || []),
            ...(this.getMetadata().get(`scopes.${this.entityType}.canceledStatusList`) || []),
        ];
    }

    setupAfterModelCreated() {
        super.setupAfterModelCreated();

        const buttonData = this.getAcceptanceButtonData();

        this.addButton({
            name: 'setAcceptanceStatus',
            html: buttonData.html,
            hidden: this.hasAcceptanceStatusButton(),
            style: buttonData.style,
            className: 'btn-text',
            pullLeft: true,
            onClick: () => this.actionSetAcceptanceStatus(),
        }, 'cancel');

        if (
            !this.getAcl().getScopeForbiddenFieldList(this.model.entityType).includes('status')
        ) {
            this.addDropdownItem({
                name: 'setHeld',
                text: this.translate('Set Held', 'labels', this.model.entityType),
                hidden: true,
                iconClass: 'fas fa-check',
            });

            this.addDropdownItem({
                name: 'setNotHeld',
                text: this.translate('Set Not Held', 'labels', this.model.entityType),
                hidden: true,
            });
        }

        this.addDropdownItem({
            name: 'sendInvitations',
            text: this.translate('Send Invitations', 'labels', 'Meeting'),
            hidden: !this.isSendInvitationsToBeDisplayed(),
            onClick: () => this.actionSendInvitations(),
            groupIndex: 10,
            iconClass: 'far fa-paper-plane',
        });

        this.initAcceptanceStatus();

        this.on('switch-model', (model, previousModel) => {
            this.stopListening(previousModel, 'sync');
            this.initAcceptanceStatus();
        });

        this.on('after:save', () => {
            if (this.hasAcceptanceStatusButton()) {
                this.showAcceptanceButton();
            } else {
                this.hideAcceptanceButton();
            }

            if (this.isSendInvitationsToBeDisplayed()) {
                this.showActionItem('sendInvitations');
            } else {
                this.hideActionItem('sendInvitations');
            }
        });

        this.listenTo(this.model, 'sync', () => {
            if (this.isSendInvitationsToBeDisplayed()) {
                this.showActionItem('sendInvitations');

                return;
            }

            this.hideActionItem('sendInvitations');
        });

        this.listenTo(this.model, 'after:save', () => {
            if (this.isSendInvitationsToBeDisplayed()) {
                this.showActionItem('sendInvitations');

                return;
            }

            this.hideActionItem('sendInvitations');
        });
    }

    controlRecordButtonsVisibility() {
        super.controlRecordButtonsVisibility();

        this.controlStatusActionVisibility();
    }

    controlStatusActionVisibility() {
        this.setupStatuses();

        if (
            this.getAcl().check(this.model, 'edit') &&
            !this.notActualStatusList.includes(this.model.get('status'))
        ) {
            this.showActionItem('setHeld');
            this.showActionItem('setNotHeld');

            return;
        }

        this.hideActionItem('setHeld');
        this.hideActionItem('setNotHeld');
    }

    initAcceptanceStatus() {
        if (this.hasAcceptanceStatusButton()) {
            this.showAcceptanceButton();
        } else {
            this.hideAcceptanceButton();
        }

        this.listenTo(this.model, 'sync', () => {
            if (this.hasAcceptanceStatusButton()) {
                this.showAcceptanceButton();
            } else {
                this.hideAcceptanceButton();
            }
        });
    }

    /**
     *
     * @return {{
     *     style: 'default'|'danger'|'success'|'warning'|'info',
     *     html: string,
     *     text: string,
     * }}
     */
    getAcceptanceButtonData() {
        const acceptanceStatus = this.model.getLinkMultipleColumn('users', 'status', this.getUser().id);

        let text;
        let style = 'default';

        let iconHtml = null;

        if (acceptanceStatus && acceptanceStatus !== 'None') {
            text = this.getLanguage().translateOption(acceptanceStatus, 'acceptanceStatus', this.model.entityType);

            style = this.getMetadata()
                .get(['entityDefs', this.model.entityType,
                    'fields', 'acceptanceStatus', 'style', acceptanceStatus]);

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
        } else {
            text = typeof acceptanceStatus !== 'undefined' ?
                this.translate('Acceptance', 'labels', 'Meeting') :
                ' ';
        }

        let html = this.getHelper().escapeString(text);

        if (iconHtml) {
            html = iconHtml + ' ' + html;
        }

        return {
            style: style,
            text: text,
            html: html,
        };
    }

    showAcceptanceButton() {
        this.showActionItem('setAcceptanceStatus');
        this.updateActionItem('setAcceptanceStatus', this.getAcceptanceButtonData());
    }

    hideAcceptanceButton() {
        this.hideActionItem('setAcceptanceStatus');
    }

    hasAcceptanceStatusButton() {
        if (!this.model.has('status')) {
            return false;
        }

        if (!this.model.has('usersIds')) {
            return false;
        }

        if (this.notActualStatusList.includes(this.model.get('status'))) {
            return false;
        }

        if (!~this.model.getLinkMultipleIdList('users').indexOf(this.getUser().id)) {
            return false;
        }
        return true;
    }

    actionSetAcceptanceStatus() {
        this.createView('dialog', 'crm:views/meeting/modals/acceptance-status', {
            model: this.model,
        }, (view) => {
            view.render();

            this.listenTo(view, 'set-status', (status) => {
                this.hideAcceptanceButton();

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

                                setTimeout(() => {
                                    this.$el.find(`button[data-name="setAcceptanceStatus"]`).focus();
                                }, 50)
                            });
                    });
            });
        });
    }

    actionSetHeld() {
        this.model.save({status: 'Held'});

        // Needed for calendar update.
        this.trigger('after:save', this.model);
    }

    actionSetNotHeld() {
        this.model.save({status: 'Not Held'});

        // Needed for calendar update.
        this.trigger('after:save', this.model);
    }

    isSendInvitationsToBeDisplayed() {
        if (this.notActualStatusList.includes(this.model.get('status'))) {
            return false;
        }

        const dateEnd = this.model.get('dateEnd');

        if (
            dateEnd &&
            this.getDateTime().toMoment(dateEnd).isBefore(moment.now())
        ) {
            return false;
        }

        if (!this.getAcl().checkModel(this.model, 'edit')) {
            return false;
        }

        const userIdList = this.model.getLinkMultipleIdList('users');
        const contactIdList = this.model.getLinkMultipleIdList('contacts');
        const leadIdList = this.model.getLinkMultipleIdList('leads');

        if (!contactIdList.length && !leadIdList.length && !userIdList.length) {
            return false;
        }

        return true;
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
}

export default MeetingModalDetailView;
