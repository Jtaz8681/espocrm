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
import StreamListView from 'views/stream/record/list';
import SearchView from 'views/record/search';
import SearchManager from 'search-manager';

class GlobalStreamView extends View {

    // language=Handlebars
    templateContent = `
        <div class="page-header">
            <div class="row">
                <div class="col-sm-7 col-xs-5">
                    <h3>
                        <span
                            data-action="fullRefresh"
                            style="user-select: none; cursor: pointer"
                        >{{translate 'GlobalStream' category='scopeNames'}}</span>
                    </h3>
                </div>
                <div class="col-sm-5 col-xs-7"></div>
            </div>
        </div>
        <div class="search-container">{{{search}}}</div>
        <div class="row">
            <div class="col-md-8">
                <div class="list-container list-container-panel">{{{list}}}</div>
            </div>
        </div>
    `

    collection

    setup() {
        this.wait(
            (async () => {
                this.collection = await this.getCollectionFactory().create('Note');

                this.collection.url = 'GlobalStream';
                this.collection.maxSize = this.getConfig().get('recordsPerPage');
                this.collection.paginationByNumber = true;

                this.setupSearchManager();
                await this.createSearchView();
            })()
        );

        this.addActionHandler('fullRefresh', () => this.actionFullRefresh());
    }

    setupSearchManager() {
        const searchManager = new SearchManager(this.collection);

        searchManager.loadStored();

        this.collection.where = searchManager.getWhere();
        this.searchManager = searchManager;
    }

    createSearchView() {
        this.searchView = new SearchView({
            collection: this.collection,
            searchManager: this.searchManager,
            isWide: true,
            filtersLayoutName: 'filtersGlobal',
        });

        return this.assignView('search', this.searchView, '.search-container');
    }

    afterRender() {
        if (!this.listView) {
            this.fetchAndRender();
        }
    }

    fetchAndRender() {
        Espo.Ui.notifyWait();

        this.collection.fetch()
            .then(() => {
                this.listView = new StreamListView({
                    collection: this.collection,
                    isUserStream: true,
                });

                this.assignView('list', this.listView, '.list-container')
                    .then(() => {
                        Espo.Ui.notify(false);

                        this.listView.render();
                    });
            });
    }

    /**
     * @private
     */
    async actionFullRefresh() {
        Espo.Ui.notifyWait();

        await this.collection.fetch();

        Espo.Ui.notify();
    }
}

export default GlobalStreamView;
