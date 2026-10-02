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

import Utils from 'utils';
import ModalView from 'views/modal';
import Collection from 'collection';
import Ajax from 'ajax';
import Ui from 'ui';

export default class SendInvitationsModalView extends ModalView {

    backdrop = true

    templateContent = `
        <div class="margin-bottom">
            <p>{{message}}</p>
        </div>
        <div class="list-container">{{{list}}}</div>
    `

    data() {
        return {
            message: this.translate('sendInvitationsToSelectedAttendees', 'messages', 'Meeting'),
        };
    }

    setup() {
        this.shortcutKeys = {};
        this.shortcutKeys['Control+Enter'] = e => {
            if (!this.hasAvailableActionItem('send')) {
                return;
            }

            e.preventDefault();

            this.actionSend();
        };

        this.$header = $('<span>').append(
            $('<span>')
                .text(this.translate(this.model.entityType, 'scopeNames')),
            ' <span class="chevron-right"></span> ',
            $('<span>')
                .text(this.model.get('name')),
            ' <span class="chevron-right"></span> ',
            $('<span>')
                .text(this.translate('Send Invitations', 'labels', 'Meeting'))
        );

        this.addButton({
            label: 'Send',
            name: 'send',
            style: 'danger',
            disabled: true,
        });

        this.addButton({
            label: 'Cancel',
            name: 'cancel',
        });

        this.collection = new Collection();
        this.collection.url = this.model.entityType + `/${this.model.id}/attendees`;

        this.wait(
            this.prepareList()
        );
    }

    /**
     * @private
     * @return {Promise<void>}
     */
    async prepareList() {
        await this.collection.fetch();

        Utils.clone(this.collection.models).forEach(model => {
            model.entityType = model.get('_scope');

            if (!model.get('emailAddress')) {
                this.collection.remove(model.id);
            }
        });

        const view = await this.createView('list', 'views/record/list', {
            selector: '.list-container',
            collection: this.collection,
            rowActionsDisabled: true,
            massActionsDisabled: true,
            checkAllResultDisabled: true,
            selectable: true,
            buttonsDisabled: true,
            listLayout: [
                {
                    name: 'name',
                    customLabel: this.translate('name', 'fields'),
                    notSortable: true,
                },
                {
                    name: 'acceptanceStatus',
                    width: 40,
                    customLabel: this.translate('acceptanceStatus', 'fields', 'Meeting'),
                    notSortable: true,
                    view: 'views/fields/enum',
                    params: {
                        options: this.model.getFieldParam('acceptanceStatus', 'options'),
                        style: this.model.getFieldParam('acceptanceStatus', 'style'),
                    },
                },
            ],
        });

        this.collection.models
            .filter(model => {
                const status = model.get('acceptanceStatus');

                return !status || status === 'None';
            })
            .forEach(model => {
                this.getListView().checkRecord(model.id);
            });

        this.listenTo(view, 'check', () => this.controlSendButton());

        this.controlSendButton();
    }

    controlSendButton() {
        this.getListView().getCheckedIds().length ?
            this.enableButton('send') :
            this.disableButton('send');
    }

    /**
     * @return {import('views/record/list').default}
     */
    getListView() {
        return this.getView('list');
    }

    actionSend() {
        this.disableButton('send');

        Espo.Ui.notifyWait();

        const targets = this.getListView().getCheckedIds()
            .map(id => this.collection.get(id));

        Ajax
            .postRequest(`${this.model.entityType}/action/sendInvitations`, {
                id: this.model.id,
                targets: targets.map(m => {
                    return {
                        entityType: m.entityType,
                        id: m.id,
                    }
                }),
            })
            .then(/** {idList: string[]} */result => {
                if (result.idList.length === 0) {
                    Ui.warning(this.translate('nothingHasBeenSent', 'messages', 'Meeting'));
                } else if (result.idList.length === targets.length) {
                    Ui.success(this.translate('Sent'));
                } else {
                    const recipientsString = targets
                        .filter(m => result.idList.includes(m.id))
                        .map(m => m.attributes.name ?? m.attributes.id)
                        .join(', ');

                    const failedRecipientsString = targets
                        .filter(m => !result.idList.includes(m.id))
                        .map(m => m.attributes.name ?? m.attributes.id)
                        .join(', ');

                    const message = this.translate('invitationsSentTo', 'messages', 'Meeting')
                        .replace('{recipients}', recipientsString)
                        .replace('{failedRecipients}', failedRecipientsString);

                    Ui.notify(message, 'warning', null, {
                        closeButton: true,
                    });
                }

                this.trigger('sent');
                this.close();
            })
            .catch(() => {
                this.enableButton('send');
            });
    }
}
