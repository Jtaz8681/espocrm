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
import SearchManager from 'search-manager';

class ActionHistoryModalView extends ModalView {

    template = 'modals/action-history'

    scope = 'ActionHistoryRecord'
    className = 'dialog dialog-record'
    backdrop = true

    setup() {
        super.setup();

        this.buttonList = [
            {
                name: 'cancel',
                label: 'Close',
            },
        ];

        this.scope = this.entityType = this.options.scope || this.scope;

        this.$header = $('<a>')
            .attr('href', '#ActionHistoryRecord')
            .addClass('action')
            .attr('data-action', 'listView')
            .text(this.getLanguage().translate(this.scope, 'scopeNamesPlural'));

        this.waitForView('list');

        this.getCollectionFactory().create(this.scope, collection => {
            collection.maxSize = this.getConfig().get('recordsPerPage') || 20;
            this.collection = collection;

            this.setupSearch();
            this.setupList();

            collection.fetch();
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionListView() {
        this.getRouter().navigate('#ActionHistoryRecord', {trigger: true});
        this.close();
    }

    setupSearch() {
        this.searchManager = new SearchManager(this.collection);

        this.collection.data.boolFilterList = ['onlyMy'];
        this.collection.where = this.searchManager.getWhere();

        this.createView('search', 'views/record/search', {
            collection: this.collection,
            fullSelector: this.containerSelector + ' .search-container',
            searchManager: this.searchManager,
            disableSavePreset: true,
            textFilterDisabled: true,
        });
    }

    setupList() {
        const viewName = this.getMetadata().get(`clientDefs.${this.scope}.recordViews.list`) ||
            'views/record/list';

        this.listenToOnce(this.collection, 'sync', () => {
            this.createView('list', viewName, {
                collection: this.collection,
                fullSelector: this.containerSelector + ' .list-container',
                selectable: false,
                checkboxes: false,
                massActionsDisabled: true,
                rowActionsView: 'views/record/row-actions/view-only',
                type: 'listSmall',
                searchManager: this.searchManager,
                checkAllResultDisabled: true,
                buttonsDisabled: true,
            });
        });
    }
}

export default ActionHistoryModalView;
