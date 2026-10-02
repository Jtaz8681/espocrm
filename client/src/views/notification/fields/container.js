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
import NotificationListRecordView from 'views/notification/record/list';
import NotificationPanelView from 'views/notification/panel';
import Ajax from 'ajax';
import Ui from 'ui';

class NotificationContainerFieldView extends BaseFieldView {

    type = 'notification'

    listTemplate = 'notification/fields/container'
    detailTemplate = 'notification/fields/container'

    /**
     * @private
     * @type {string[]}
     */
    types = [
        'Assign',
        'EmailReceived',
        'EntityRemoved',
        'Message',
        'System',
        'UserReaction',
        'Collaborating',
    ]

    inlineEditDisabled = true

    /**
     * @private
     * @type {boolean}
     */
    isGroupExpanded = false

    /**
     * @private
     * @type {boolean}
     */
    groupingEnabled

    data() {
        const count = this.model.attributes.groupedCount ?? 0;

        return {
            hasGrouped: count > 1 || count < 0,
            isGroupExpanded: this.isGroupExpanded,
            hasMarkGroupRead: this.groupingEnabled && !this.model.attributes.read,
        };
    }

    setup() {
        this.groupingEnabled = this.options.groupingEnabled ?? false;

        if (this.model.attributes.groupType) {
            this.wait(this.processGroup());
        } else {
            switch (this.model.attributes.type) {
                case 'Note':
                    this.processNote(this.model.attributes.noteData);

                    break;

                case 'MentionInPost':
                    this.processMentionInPost(this.model.attributes.noteData);

                    break;

                default:
                    this.process();
            }
        }

        this.addActionHandler('showGrouped', () => this.showGrouped());
        this.addActionHandler('markGroupRead', () => this.markGroupRead());

        if (this.model.collection) {
            this.listenTo(this.model.collection, 'all-read', () => {
                this.removeMarkGroupRead();
            });
        }
    }

    /**
     * @private
     */
    removeMarkGroupRead() {
        if (!this.isRendered()) {
            return;
        }

        const element = this.element?.querySelector('[data-action="markGroupRead"]');

        element?.remove()
    }

    process() {
        let type = this.model.get('type');

        if (!type) {
            return;
        }

        type = Espo.Utils.upperCaseFirst(type.replace(/ /g, ''));

        let viewName = this.getMetadata().get(`clientDefs.Notification.itemViews.${type}`);

        if (!viewName) {
            if (!this.types.includes(type)) {
                return;
            }

            viewName = 'views/notification/items/' + Espo.Utils.camelCaseToHyphen(type);
        }

        const parentSelector = this.options.containerSelector ?? this.getSelector();

        this.createView('notification', viewName, {
            model: this.model,
            fullSelector: `${parentSelector} li[data-id="${this.model.id}"]`,
        });
    }

    /**
     * @private
     */
    async processGroup() {
        const groupType = this.model.attributes.groupType;

        let viewName;

        if (groupType === 'Record') {
            viewName = 'views/notification/items/group-note';
        } else if (groupType === 'EmailReceived') {
            viewName = 'views/notification/items/group-email-received';
        }

        if (!viewName) {
            return;
        }

        const parentSelector = this.options.containerSelector ?? this.getSelector();

        await this.createView('notification', viewName, {
            model: this.model,
            fullSelector: `${parentSelector} li[data-id="${this.model.id}"]`,
        });
    }

    /**
     * @private
     * @param {Record} data
     */
    processNote(data) {
        if (!data) {
            return;
        }

        this.wait(true);

        this.getModelFactory().create('Note', model => {
            model.set(data);

            let viewName = this.getMetadata().get(`clientDefs.Note.itemViews.${data.type}`);

            if (!viewName) {
                // @todo Check if type exists.
                viewName = 'views/stream/notes/' + Espo.Utils.camelCaseToHyphen(data.type);
            }

            const parentSelector = this.options.containerSelector ?? this.getSelector();

            this.createView('notification', viewName, {
                model: model,
                isUserStream: true,
                fullSelector: `${parentSelector} li[data-id="${this.model.id}"] .cell[data-name="data"]`,
                onlyContent: true,
                isNotification: true,
                isInGroup: this.options.isInGroup ?? false,
            });

            this.wait(false);
        });
    }

    /**
     * @private
     * @param {Record} data
     */
    processMentionInPost(data) {
        if (!data) {
            return;
        }

        this.wait(true);

        this.getModelFactory().create('Note', model => {
            model.set(data);

            const viewName = 'views/stream/notes/mention-in-post';

            const parentSelector = this.options.containerSelector ?? this.getSelector();

            this.createView('notification', viewName, {
                model: model,
                userId: this.model.get('userId'),
                isUserStream: true,
                fullSelector: `${parentSelector} li[data-id="${this.model.id}"]`,
                onlyContent: true,
                isNotification: true,
            });

            this.wait(false);
        });
    }

    /**
     * @private
     */
    async showGrouped() {
        const collection = await this.getCollectionFactory().create('Notification');

        if (this.model.attributes.groupType) {
            collection.url = `Notification/group?type=${this.model.attributes.groupType}&id=` +
                this.model.id;

            collection.maxSize = this.getConfig().get('recordsPerPageSmall');
        } else {
            collection.url = `Notification/${this.model.id}/group`;
        }

        const button = this.element.querySelector('a[data-action="showGrouped"]');

        if (button instanceof HTMLElement) {
            button.classList.add('disabled');
        }

        Ui.notifyWait();

        try {
            await collection.fetch();
        } catch (e) {
            await this.reRender();

            return;
        }

        Ui.notify();

        this.isGroupExpanded = true;

        const view = new NotificationListRecordView({
            collection: collection,
            showCount: false,
            selector: '.notification-grouped',
            listLayout: {
                rows: [
                    [
                        {
                            name: 'data',
                            view: 'views/notification/fields/container',
                            options: {
                                isInGroup: true,
                            },
                        },
                    ],
                ],
            },
        });

        await this.assignView('groupedList', view);

        await this.reRender();

        this.triggerUpdateRead();

        if (this.model.collection) {
            view.listenTo(this.model.collection, 'all-read', () => {
                collection.models.forEach(model => {
                    model.set('read', true, {sync: true});
                });
            });
        }
    }

    /**
     * @private
     */
    async markGroupRead() {
        await Ajax.postRequest(`Notification/group/${this.model.id}/markRead`);

        this.model.set('read', true, {sync: true});

        await this.reRender();

        this.triggerUpdateRead();
    }

    /**
     * @internal
     */
    triggerUpdateRead() {
        let viewPointer = this;

        while (true) {
            viewPointer = viewPointer.getParentView();

            if (!viewPointer || viewPointer instanceof NotificationPanelView) {
                break;
            }
        }

        if (viewPointer instanceof NotificationPanelView) {
            viewPointer.trigger('collection-fetched');
        }
    }
}

export default NotificationContainerFieldView;
