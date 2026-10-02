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

/** @module views/record/list-nested-categories */

import View from 'view';
import RecordModalHelper from 'helpers/record-modal';

class ListNestedCategoriesRecordView extends View {

    template = 'record/list-nested-categories'

    isLoading = false

    events = {
        'click .action': function (e) {
            Espo.Utils.handleAction(this, e.originalEvent, e.currentTarget);
        },
    }

    /**
     * @type {import('collections/tree').default}
     */
    collection

    constructor(options) {
        super(options);

        this.collection = options.collection;
    }

    /**
     * @type {import('collection').default}
     */
    itemCollection

    /**
     * @type {boolean}
     */
    hasNavigationPanel

    /**
     * @type {boolean}
     */
    isExpanded

    /**
     * @protected
     * @type {string}
     */
    subjectEntityType

    /**
     * @protected
     * @type {string}
     */
    categoryEntityType

    /**
     * @private
     */
    showCreate

    data() {
        const data = {};

        if (!this.isLoading) {
            data.list = this.getDataList();
        }

        data.scope = this.collection.entityType;
        data.isExpanded = this.isExpanded;
        data.isLoading = this.isLoading;
        data.currentId = this.collection.currentCategoryId;
        data.currentName = this.collection.currentCategoryName;
        data.categoryData = this.collection.categoryData;
        data.showFolders = !this.isExpanded;
        data.hasExpandedToggler = this.options.hasExpandedToggler;
        data.showEditLink = this.options.showEditLink;
        data.showCreate = this.showCreate;
        data.hasNavigationPanel = this.hasNavigationPanel;

        data.createCategoryLabel =
            this.translate(`Create ${this.categoryEntityType}`, 'labels', this.categoryEntityType);

        const categoryData = this.collection.categoryData || {};

        if (this.showCreate) {
            data.createLink = `#${this.categoryEntityType}/create`;

            let createReturnUrl = `#${this.subjectEntityType}`;

            if (categoryData.id) {
                createReturnUrl += `/list/categoryId=${categoryData.id}`;
            }

            data.createLink +=  `?returnUrl=${encodeURIComponent(createReturnUrl)}`;

            if (categoryData.id) {
                data.createLink += `&parentId=${categoryData.id}&parentName=${categoryData.name}`;
            }
        }

        data.upperLink = categoryData.upperId ?
            '#' + this.subjectEntityType + '/list/categoryId=' + categoryData.upperId:
            '#' + this.subjectEntityType;

        if (this.options.primaryFilter) {
            const part = 'primaryFilter=' + this.getHelper().escapeString(this.options.primaryFilter);

            if (categoryData.upperId) {
                data.upperLink += '&' + part;
            } else {
                data.upperLink += '/list/' + part;
            }
        }

        data.isExpandedResult = data.isExpanded ||
            this.itemCollection.data.textFilter ||
            (
                this.itemCollection.where &&
                this.itemCollection.where.find(it => it.type === 'textFilter')
            );

        return data;
    }

    /**
     * @private
     * @return {{
     *     id: string,
     *     name: string,
     *     recordCount: number,
     *     isEmpty: boolean,
     *     link: string,
     * }[]}
     */
    getDataList() {
        const list = [];

        this.collection.forEach(model => {
            let url = `#${this.subjectEntityType}/list/categoryId=${model.id}`;

            if (this.options.primaryFilter) {
                url += '&primaryFilter=' + this.getHelper().escapeString(this.options.primaryFilter);
            }

            const o = {
                id: model.id,
                name: model.get('name'),
                recordCount: model.get('recordCount'),
                isEmpty: model.get('isEmpty'),
                link: url,
            };

            list.push(o);
        });

        return list;
    }

    setup() {
        this.isExpanded = this.options.isExpanded;
        this.subjectEntityType = this.options.subjectEntityType;
        this.hasNavigationPanel = this.options.hasNavigationPanel;
        this.itemCollection = this.options.itemCollection;

        this.categoryEntityType = this.collection.entityType;

        this.showCreate = this.getAcl().check(this.categoryEntityType, 'create');

        this.listenTo(this.collection, 'sync', () => this.reRender());
        this.listenTo(this.itemCollection, 'sync', () => this.reRender());

        this.addActionHandler('createCategory', event => this.handleCreateCategory(event));
    }

    // noinspection JSUnusedGlobalSymbols
    actionShowMore() {
        this.$el.find('.category-item.show-more').addClass('hidden');

        this.collection.fetch({
            remove: false,
            more: true,
        });
    }

    /**
     * @param {MouseEvent} event
     * @private
     */
    async handleCreateCategory(event) {
        event.preventDefault();

        const categoryData = this.collection.categoryData || {};

        const view = await new RecordModalHelper().showCreate(this, {
            entityType: this.categoryEntityType,
            attributes: {
                parentId: categoryData.id ?? null,
                parentName: categoryData.name ?? null,
            },
            rootUrl: this.getRouter().getCurrentUrl(),
            afterSave: () => {
                this.collection.fetch();
            },
        });

        await view.render();
    }
}

// noinspection JSUnusedGlobalSymbols
export default ListNestedCategoriesRecordView;
