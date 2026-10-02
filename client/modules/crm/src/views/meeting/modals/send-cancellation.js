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

define('crm:views/meeting/modals/send-cancellation', ['views/modal', 'collection'], function (Dep, Collection) {
    /**
     * @module crm_views/meeting/modals/send-cancellation
     */

    /**
     * @class
     * @name Class
     * @extends module:views/modal
     * @memberOf module:crm_views/meeting/modals/send-cancellation
     */
    return Dep.extend(/** @lends module:crm_views/meeting/modals/send-cancellation.Class# */{

        backdrop: true,

        templateContent: `
            <div class="margin-bottom">
                <p>{{message}}</p>
            </div>
            <div class="list-container">{{{list}}}</div>
        `,

        data: function () {
            return {
                message: this.translate('sendCancellationsToSelectedAttendees', 'messages', 'Meeting'),
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

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
                    .text(this.translate('Send Cancellation', 'labels', 'Meeting'))
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
                this.collection.fetch()
                    .then(() => {
                        Espo.Utils.clone(this.collection.models).forEach(model => {
                            model.entityType = model.get('_scope');

                            if (!model.get('emailAddress')) {
                                this.collection.remove(model.id);
                            }
                        });

                        return this.createView('list', 'views/record/list', {
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
                        })
                    })
                    .then(view => {
                        this.collection.models
                            .filter(model => {
                                if (model.id === this.getUser().id && model.entityType === 'User') {
                                    return false;
                                }

                                return true;
                            })
                            .forEach(model => {
                                this.getListView().checkRecord(model.id);
                            });

                        this.listenTo(view, 'check', () => this.controlSendButton());

                        this.controlSendButton();
                    })
            );
        },

        controlSendButton: function () {
            this.getListView().checkedList.length ?
                this.enableButton('send') :
                this.disableButton('send');
        },

        /**
         * @return {module:views/record/list}
         */
        getListView: function () {
            return this.getView('list');
        },

        actionSend: function () {
            this.disableButton('send');

            Espo.Ui.notifyWait();

            let targets = this.getListView().checkedList.map(id => {
                return {
                    entityType: this.collection.get(id).entityType,
                    id: id,
                };
            });

            Espo.Ajax
                .postRequest(this.model.entityType + '/action/sendCancellation', {
                    id: this.model.id,
                    targets: targets,
                })
                .then(result => {
                    result ?
                        Espo.Ui.success(this.translate('Sent')) :
                        Espo.Ui.warning(this.translate('nothingHasBeenSent', 'messages', 'Meeting'));

                    this.trigger('sent');

                    this.close();
                })
                .catch(() => {
                    this.enableButton('send');
                });
        },
    });
});
