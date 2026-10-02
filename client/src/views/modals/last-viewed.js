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

import ModalView from 'views/modal';

class LastViewedModalView extends ModalView {

    scope = 'ActionHistoryRecord'
    className = 'dialog dialog-record'
    template = 'modals/last-viewed'
    backdrop = true

    setup() {
        this.events['click .list .cell > a'] = () => {
            this.close();
        };

        this.$header = $('<a>')
            .attr('href', '#LastViewed')
            .attr('data-action', 'listView')
            .addClass('action')
            .text(this.getLanguage().translate('LastViewed', 'scopeNamesPlural'));

        this.waitForView('list');

        this.getCollectionFactory().create(this.scope, collection => {
            collection.maxSize = this.getConfig().get('recordsPerPage');
            collection.url = 'LastViewed';

            this.collection = collection;

            this.loadList();

            collection.fetch();
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionListView() {
        this.getRouter().navigate('#LastViewed', {trigger: true});

        this.close();
    }

    loadList() {
        const viewName = 'views/record/list';

        this.listenToOnce(this.collection, 'sync', () => {
            this.createView('list', viewName, {
                collection: this.collection,
                fullSelector: this.containerSelector + ' .list-container',
                selectable: false,
                checkboxes: false,
                massActionsDisabled: true,
                rowActionsView: false,
                checkAllResultDisabled: true,
                buttonsDisabled: true,
                headerDisabled: true,
                layoutName: 'listForLastViewed',
                layoutAclDisabled: true,
            });
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default LastViewedModalView;
