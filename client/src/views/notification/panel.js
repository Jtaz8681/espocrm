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

class NotificationPanelView extends View {

    template = 'notification/panel'

    /**
     * @private
     * @type {boolean}
     */
    groupingEnabled

    setup() {
        this.addActionHandler('markAllNotificationsRead', () => this.actionMarkAllRead());
        this.addActionHandler('openNotifications', () => this.actionOpenNotifications());
        this.addActionHandler('closePanel', () => this.close());

        this.addHandler('keydown', '', /** KeyboardEvent */event => {
            if (event.code === 'Escape') {
                this.close();
            }
        })

        this.groupingEnabled = this.getPreferences().get('notificationGrouping') === true;

        const promise =
            this.getCollectionFactory().create('Notification', collection => {
                this.collection = collection;
                this.collection.maxSize = this.getConfig().get('notificationsMaxSize') || 5;

                this.listenTo(this.collection, 'sync', () => {
                    this.trigger('collection-fetched');
                });
            });

        this.wait(promise);

        this.once('remove', () => {
            if (this.collection) {
                this.collection.abortLastFetch();
            }
        });
    }

    afterRender() {
        this.collection.fetch()
            .then(() => this.createRecordView())
            .then(view => view.render());

        $('#navbar li.notifications-badge-container').addClass('open');

        this.$el.find('> .panel').focus();
    }

    onRemove() {
        $('#navbar li.notifications-badge-container').removeClass('open');
    }

    /**
     * @return {Promise<module:views/record/list-expanded>}
     */
    createRecordView() {
        const viewName = this.getMetadata().get(['clientDefs', 'Notification', 'recordViews', 'list']) ||
            'views/notification/record/list';

        return /** @type {Promise<module:views/record/list-expanded>} */this.createView('list', viewName, {
            selector: '.list-container',
            collection: this.collection,
            showCount: false,
            listLayout: {
                rows: [
                    [
                        {
                            name: 'data',
                            view: 'views/notification/fields/container',
                            options: {
                                containerSelector: this.getSelector(),
                                groupingEnabled: this.groupingEnabled,
                            },
                        }
                    ]
                ],
            },
        });
    }

    async actionMarkAllRead() {
        this.collection.trigger('all-read');

        await Espo.Ajax.postRequest('Notification/action/markAllRead');

        this.trigger('all-read')

        this.collection.models.forEach(model => {
            model.set('read', true, {sync: true});
        });
    }

    close() {
        this.trigger('close');
    }

    actionOpenNotifications() {
        this.getRouter().navigate('#Notification', {trigger: true});

        this.close();
    }
}

export default NotificationPanelView;
