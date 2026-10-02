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

/** @module views/notification/record/list */

import ListExpandedRecordView from 'views/record/list-expanded';

class NotificationListRecordView extends ListExpandedRecordView {

    /**
     * @protected
     * @type {boolean}
     */
    hasStars = true

    /**
     * @protected
     * @type {string}
     */
    starredAttribute = 'isNotRead'

    /**
     * @name collection
     * @type module:collections/note
     * @memberOf NotificationListRecordView#
     */
    setup() {
        super.setup();

        this.listenTo(this.collection, 'sync', (c, r, options) => {
            if (!options.fetchNew) {
                return;
            }

            const lengthBeforeFetch = options.lengthBeforeFetch || 0;

            if (lengthBeforeFetch === 0) {
                this.reRender();

                return;
            }

            const $list = this.$el.find(this.listContainerEl);

            const rowCount = this.collection.length - lengthBeforeFetch;

            for (let i = rowCount - 1; i >= 0; i--) {
                const model = this.collection.at(i);

                $list.prepend(
                    $(this.getRowContainerHtml(model.id))
                );

                this.buildRow(i, model, view => {
                    view.render();
                });
            }
        });

        this.events['auxclick a[href][data-scope][data-id]'] = e => {
            const isCombination = e.button === 1 && (e.ctrlKey || e.metaKey);

            if (!isCombination) {
                return;
            }

            const $target = $(e.currentTarget);

            const id = $target.attr('data-id');
            const scope = $target.attr('data-scope');

            e.preventDefault();
            e.stopPropagation();

            this.actionQuickView({
                id: id,
                scope: scope,
            });
        };
    }

    getCellSelector(model, item) {
        const current = this.getSelector();
        const row = this.getRowSelector(model.id);

        if (item.field === 'right') {
            return `${current} ${row} > .cell[data-name="${item.field}"]`;
        }

        return `${current} ${row} > .expanded-row > .cell[data-name="${item.field}"]`;
    }

    /**
     * @return {Promise}
     */
    showNewRecords() {
        if (this.isGroupingEnabled()) {
            return this.collection.fetch();
        }

        return this.collection.fetchNew();
    }

    /**
     * @todo Preferences?
     * @private
     * @return {boolean}
     */
    isGroupingEnabled() {
        return true;
    }
}

export default NotificationListRecordView;
