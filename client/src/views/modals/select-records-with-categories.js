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

import SelectRecordsModal from 'views/modals/select-records';

class SelectRecordsWithCategoriesModalView extends SelectRecordsModal {

    template = 'modals/select-records-with-categories'

    /**
     * @private
     * @type {string}
     */
    categoryField = 'category'

    /**
     * @private
     * @type {string}
     */
    categoryFilterType = 'inCategory'

    /**
     * @private
     * @type {string}
     */
    categoryScope

    /**
     * @private
     * @type {boolean}
     */
    isExpanded = true

    /**
     * @protected
     * @type {boolean}
     */
    isCategoryMultiple

    data() {
        return {
            ...super.data(),
            categoriesDisabled: this.categoriesDisabled,
        };
    }

    setup() {
        // noinspection JSUnresolvedReference
        this.scope = this.entityType = this.options.scope || this.scope || this.options.entityType;

        this.categoryScope = this.categoryScope || this.scope + 'Category';
        this.categoryField = this.getMetadata().get(`scopes.${this.categoryScope}.categoryField`) || this.categoryField;

        this.isCategoryMultiple = this.getMetadata()
            .get(`entityDefs.${this.scope}.fields.${this.categoryField}.type`) === 'linkMultiple';

        this.categoriesDisabled = this.categoriesDisabled ||
           this.getMetadata().get(['scopes',  this.categoryScope, 'disabled']) ||
           !this.getAcl().checkScope(this.categoryScope);

        super.setup();

        this.addActionHandler('toggleExpandedFromNavigation', () => this.actionToggleExpandedFromNavigation());
    }

    setupList() {
        if (!this.categoriesDisabled) {
            this.setupCategories();
        }

        super.setupList();
    }

    setupCategories() {
        this.getCollectionFactory().create(this.categoryScope, collection => {
            this.treeCollection = collection;

            collection.url = collection.entityType + '/action/listTree';
            collection.data.onlyNotEmpty = true;

            collection.fetch()
                .then(() => this.createCategoriesView());
        });
    }

    /**
     * @protected
     * @return {import('views/record/list-tree').default}
     */
    getCategoriesView() {
        return this.getView('categories');
    }

    createCategoriesView() {
        this.createView('categories', 'views/record/list-tree', {
            collection: this.treeCollection,
            selector: '.categories-container',
            selectable: true,
            readOnly: true,
            showRoot: true,
            buttonsDisabled: true,
            checkboxes: false,
            isExpanded: this.isExpanded,
        }, view => {
            if (this.isRendered()) {
                view.render();
            } else {
                this.listenToOnce(this, 'after:render', () => view.render());
            }

            this.listenTo(view, 'select', model => {
                this.currentCategoryId = null;
                this.currentCategoryName = '';

                if (model && model.id) {
                    this.currentCategoryId = model.id;
                    this.currentCategoryName = model.get('name');
                }

                this.applyCategoryToCollection();

                Espo.Ui.notifyWait();

                this.collection.fetch()
                    .then(() => Espo.Ui.notify(false));
            });
        });
    }

    /**
     * @private
     */
    async actionToggleExpandedFromNavigation() {
        this.isExpanded = !this.isExpanded;

        /** @type {HTMLAnchorElement} */
        const a = this.element.querySelector('a[data-role="expandButtonContainer"]');

        if (a) {
            a.classList.add('disabled');
        }

        this.applyCategoryToCollection();
        this.getCategoriesView().isExpanded = this.isExpanded;

        Espo.Ui.notifyWait();

        await this.collection.fetch();

        this.getCategoriesView().reRender().then(() => {});

        Espo.Ui.notify();
    }

    /**
     * @private
     * @todo Move to helper. Together with list view.
     */
    applyCategoryToCollection() {
        this.collection.whereFunction = () => {
            let filter;
            const isExpanded = this.isExpanded;

            if (!isExpanded && !this.hasTextFilter()) {
                if (this.isCategoryMultiple) {
                    if (this.currentCategoryId) {
                        filter = {
                            attribute: this.categoryField,
                            type: 'linkedWith',
                            value: [this.currentCategoryId]
                        };
                    }
                    else {
                        filter = {
                            attribute: this.categoryField,
                            type: 'isNotLinked'
                        };
                    }
                }
                else {
                    if (this.currentCategoryId) {
                        filter = {
                            attribute: this.categoryField + 'Id',
                            type: 'equals',
                            value: this.currentCategoryId
                        };
                    }
                    else {
                        filter = {
                            attribute: this.categoryField + 'Id',
                            type: 'isNull'
                        };
                    }
                }
            }
            else {
                if (this.currentCategoryId) {
                    filter = {
                        attribute: this.categoryField,
                        type: this.categoryFilterType,
                        value: this.currentCategoryId,
                    };
                }
            }

            if (filter) {
                return [filter];
            }
        };
    }

    /**
     * @private
     * @return {boolean}
     */
    hasTextFilter() {
        return !!this.collection.data.textFilter ||
            (
                this.collection.where &&
                this.collection.where.find(it => it.type === 'textFilter')
            );
    }
}

// noinspection JSUnusedGlobalSymbols
export default SelectRecordsWithCategoriesModalView;
