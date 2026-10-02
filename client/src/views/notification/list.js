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
import Ajax from 'ajax';
import Ui from 'ui';

class NotificationListView extends View {

    template = 'notification/list'

    /**
     * @private
     * @type {boolean}
     */
    groupingEnabled

    setup() {
        this.addActionHandler('refresh', () => this.actionRefresh());

        this.addActionHandler('markAllNotificationsRead', () => this.actionMarkAllRead());

        this.groupingEnabled = this.getPreferences().get('notificationGrouping') === true;

        const promise =
            this.getCollectionFactory().create('Notification')
                .then(collection => {
                    this.collection = collection;
                    this.collection.maxSize = this.getConfig().get('recordsPerPage') || 20;
                })

        this.wait(promise);
    }

    actionRefresh() {
        Ui.notifyWait();

        const $btn = this.$el.find('[data-action="refresh"]');
        $btn.addClass('disabled').attr('disabled', 'disabled');

        this.animateRefreshButton();

        this.getRecordView().showNewRecords()
            .then(() => {
                Ui.notify(false);
            })
            .finally(() => $btn.removeClass('disabled').removeAttr('disabled'));
    }

    animateRefreshButton() {
        const iconEl = this.element.querySelector('button[data-action="refresh"] span');

        if (iconEl) {
            iconEl.classList.add('animation-spin-fast');

            setTimeout(() => iconEl.classList.remove('animation-spin-fast'), 500);
        }
    }

    afterRender() {
        const viewName = this.getMetadata().get(['clientDefs', 'Notification', 'recordViews', 'list']) ||
            'views/notification/record/list';

        const options = {
            selector: '.notification-list',
            collection: this.collection,
            showCount: false,
            rowActionsView: 'views/record/row-actions/remove-only',
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
                        },
                    ],
                ],
            },
        };

        this.collection
            .fetch()
            .then(() => this.createView('list', viewName, options))
            .then(view => view.render())
            .then(view => {
                view.$el.find('> .list > .list-group');
            });
    }

    actionMarkAllRead() {
        this.collection.trigger('all-read');

        Ui.notifyWait();

        const $link = this.$el.find('[data-action="markAllNotificationsRead"]');
        $link.attr('disabled', 'disabled').addClass('disabled');

        Ajax.postRequest('Notification/action/markAllRead')
            .then(() => {
                this.trigger('all-read');
                Ui.notify(false);

                this.$el.find('.badge-circle-warning').remove();
            })
            .finally(() => $link.removeAttr('disabled').removeClass('disabled'));

        this.collection.models.forEach(model => {
            model.set('read', true, {sync: true});
        });
    }

    /**
     * @return {module:views/notification/record/list}
     */
    getRecordView() {
        return this.getView('list');
    }
}

export default NotificationListView;
