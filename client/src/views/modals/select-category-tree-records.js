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

import SelectRecordsModalView from 'views/modals/select-records';
import SearchManager from 'search-manager';

class SelectCategoryTreeRecordsModalView extends SelectRecordsModalView {

    setup() {
        this.filters = this.options.filters || {};
        this.boolFilterList = this.options.boolFilterList || {};
        this.primaryFilterName = this.options.primaryFilterName || null;

        if ('multiple' in this.options) {
            this.multiple = this.options.multiple;
        }

        this.createButton = false;
        this.massRelateEnabled = this.options.massRelateEnabled;

        this.buttonList = [
            {
                name: 'cancel',
                label: 'Cancel',
            }
        ];

        if (this.multiple) {
            this.buttonList.unshift({
                name: 'select',
                style: 'danger',
                label: 'Select',
                onClick: dialog => {
                    const listView = this.getRecordView();

                    if (listView.allResultIsChecked) {
                        const data = {
                            massRelate: true,
                            where: listView.getWhereForAllResult(),
                            searchParams: this.collection.data,
                        };

                        this.trigger('select', data);

                        if (this.options.onMassSelect) {
                            this.options.onMassSelect(data);
                        }
                    } else {
                        const list = listView.getSelected();

                        if (list.length) {
                            this.trigger('select', list);

                            if (this.options.onSelect) {
                                this.options.onSelect(list);
                            }
                        }
                    }

                    dialog.close();
                },
            });
        }

        // noinspection JSUnresolvedReference
        this.scope = this.entityType = this.options.entityType || this.options.scope;

        this.$header = $('<span>');

        this.$header.append(
            $('<span>').text(
                this.translate('Select') + ' · ' +
                this.getLanguage().translate(this.entityType, 'scopeNamesPlural')
            )
        );

        this.$header.prepend(
            this.getHelper().getScopeColorIconHtml(this.entityType)
        );

        this.waitForView('list');

        this.getCollectionFactory().create(this.entityType, collection => {
            collection.maxSize = this.getConfig().get('recordsPerPageSelect') || 5;

            this.collection = collection;

            const searchManager = new SearchManager(collection);

            searchManager.emptyOnReset = true;

            if (this.filters) {
                searchManager.setAdvanced(this.filters);
            }

            if (this.boolFilterList) {
                searchManager.setBool(this.boolFilterList);
            }

            if (this.primaryFilterName) {
                searchManager.setPrimary(this.primaryFilterName);
            }

            collection.where = searchManager.getWhere();
            collection.url = collection.entityType + '/action/listTree';

            const viewName =
                this.getMetadata().get(`clientDefs.${this.entityType}.recordViews.listSelectCategoryTree`) ||
                'views/record/list-tree';

            this.listenToOnce(collection, 'sync', () => {
                this.createView('list', viewName, {
                    collection: collection,
                    fullSelector: this.containerSelector + ' .list-container',
                    readOnly: true,
                    selectable: true,
                    checkboxes: this.multiple,
                    massActionsDisabled: true,
                    searchManager: searchManager,
                    checkAllResultDisabled: true,
                    buttonsDisabled: true,
                }, listView => {
                    listView.once('select', models => {
                        if (!Array.isArray(models)) {
                            models = [models];
                        }

                        this.trigger('select', models);

                        if (this.options.onSelect) {
                            this.options.onSelect(models);
                        }

                        this.close();
                    });
                });
            });

            collection.fetch();
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default SelectCategoryTreeRecordsModalView;
