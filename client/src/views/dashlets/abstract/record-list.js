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

import BaseDashletView from 'views/dashlets/abstract/base';
import SearchManager from 'search-manager';
import RecordModal from 'helpers/record-modal';

class RecordListDashletView extends BaseDashletView {

    templateContent = '<div class="list-container">{{{list}}}</div>'

    /**
     * @protected
     * @type {string[]}
     */
    additionalRowActionList = undefined

    /**
     * A scope.
     * @type {string}
     */
    scope

    listView = null
    listViewColumn = 'views/record/list'
    listViewExpanded = 'views/record/list-expanded'
    layoutType = 'expanded'

    optionsFields = {
        title: {
            type: 'varchar',
            required: true,
        },
        autorefreshInterval: {
            type: 'enumFloat',
            options: [0, 0.5, 1, 2, 5, 10],
        },
        displayRecords: {
            type: 'enumInt',
            options: [3, 4, 5, 10, 15],
        },
    }

    rowActionsView = 'views/record/row-actions/view-and-edit'

    /**
     * @protected
     * @type {boolean}
     */
    hasCollaborators

    init() {
        this.name = this.options.name || this.name;
        this.scope = this.getMetadata().get(`dashlets.${this.name}.entityType`) || this.scope;

        this.additionalRowActionList = this.getMetadata().get(`dashlets.${this.name}.rowActionList`) ||
            this.additionalRowActionList;

        this.hasCollaborators = !!this.getMetadata().get(`scopes.${this.scope}.collaborators`);

        super.init();
    }

    checkAccess() {
        return this.getAcl().check(this.scope, 'read');
    }

    /**
     * @return {module:search-manager~data}
     */
    getSearchData() {
        /** @type {module:search-manager~data} */
        const data = Espo.Utils.cloneDeep(this.getOption('searchData'));

        if (!this.hasCollaborators) {
            return data;
        }

        if (this.getOption('includeShared')) {
            if (!data.bool) {
                data.bool = {};
            }

            data.bool.shared = true;
        }

        return data;
    }

    afterRender() {
        this.getCollectionFactory().create(this.scope, collection => {
            const searchData = this.getSearchData();

            this.searchManager = new SearchManager(collection, {defaultData: searchData});

            if (!this.scope) {
                this.$el.find('.list-container')
                    .html(this.translate('selectEntityType', 'messages', 'DashletOptions'));

                return;
            }

            if (!this.checkAccess()) {
                this.$el.find('.list-container').html(this.translate('No Access'));

                return;
            }

            if (this.collectionUrl) {
                collection.url = this.collectionUrl;
            }

            this.collection = collection;

            collection.orderBy = this.getOption('orderBy') || this.getOption('sortBy') || this.collection.orderBy;

            if (this.getOption('orderBy')) {
                collection.order = 'asc';
            }

            if (this.hasOption('asc')) {
                collection.order = this.getOption('asc') ? 'asc' : false;
            }

            if (this.getOption('sortDirection') === 'asc') {
                collection.order = 'asc';
            } else if (this.getOption('sortDirection') === 'desc') {
                collection.order = 'desc';
            }

            if (this.getOption('order') === 'asc') {
                collection.order = 'asc';
            }
            else if (this.getOption('order') === 'desc') {
                collection.order = 'desc';
            }

            collection.maxSize = this.getOption('displayRecords');
            collection.where = this.searchManager.getWhere();

            const viewName = this.listView || ((this.layoutType === 'expanded') ?
                this.listViewExpanded : this.listViewColumn);

            this.createView('list', viewName, {
                collection: collection,
                selector: '.list-container',
                pagination: !!this.getOption('pagination'),
                type: 'listDashlet',
                rowActionsView: this.rowActionsView,
                checkboxes: false,
                showMore: true,
                listLayout: this.getOption(this.layoutType + 'Layout'),
                skipBuildRows: true,
                additionalRowActionList: this.additionalRowActionList,
            }, (view) => {
                view.getSelectAttributeList(selectAttributeList => {
                    if (selectAttributeList) {
                        collection.data.select = selectAttributeList.join(',');
                    }

                    collection.fetch();
                });
            });
        });
    }

    setupActionList() {
        if (this.scope && this.getAcl().checkScope(this.scope, 'create')) {
            this.actionList.unshift({
                name: 'create',
                text: this.translate('Create ' + this.scope, 'labels', this.scope),
                iconClass: 'fas fa-plus',
                url: `#${this.scope}/create`,
            });
        }
    }

    actionRefresh() {
        this.refreshInternal();
    }

    autoRefresh() {
        this.refreshInternal({skipNotify: true});
    }

    /**
     * @private
     * @param {{skipNotify?: boolean}} [options]
     * @return {Promise<void>}
     */
    async refreshInternal(options = {}) {
        if (!this.collection) {
            return;
        }

        if (!options.skipNotify) {
            Espo.Ui.notifyWait();
        }

        this.collection.where = this.searchManager.getWhere();

        await this.collection.fetch({
            previousDataList: this.collection.models.map(model => {
                return Espo.Utils.cloneDeep(model.attributes);
            }),
        });

        if (!options.skipNotify) {
            Espo.Ui.notify();
        }
    }

    // noinspection JSUnusedGlobalSymbols
    actionCreate() {
        const attributes = this.getCreateAttributes() || {};

        if (this.getOption('populateAssignedUser')) {
            if (this.getMetadata().get(['entityDefs', this.scope, 'fields', 'assignedUsers'])) {
                attributes['assignedUsersIds'] = [this.getUser().id];
                attributes['assignedUsersNames'] = {};
                attributes['assignedUsersNames'][this.getUser().id] = this.getUser().get('name');
            } else {
                attributes['assignedUserId'] = this.getUser().id;
                attributes['assignedUserName'] = this.getUser().get('name');
            }
        }

        const helper = new RecordModal();

        helper.showCreate(this, {
            entityType: this.scope,
            attributes: attributes,
            afterSave: () => this.actionRefresh(),
        });
    }

    getCreateAttributes() {}

    getColor() {
        if (!this.scope) {
            return null;
        }

        return this.getMetadata().get(`clientDefs.${this.scope}.color`);
    }
}

export default RecordListDashletView;
