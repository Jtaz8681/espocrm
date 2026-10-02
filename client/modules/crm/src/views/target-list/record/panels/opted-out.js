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

define('crm:views/target-list/record/panels/opted-out',  ['views/record/panels/relationship', 'multi-collection'],
function (Dep, MultiCollection) {

    return Dep.extend({

        name: 'optedOut',

        template: 'crm:target-list/record/panels/opted-out',

        scopeList: ['Contact', 'Lead', 'User', 'Account'],

        data: function () {
            return {
                currentTab: this.currentTab,
                scopeList: this.scopeList,
            };
        },

        getStorageKey: function () {
            return 'target-list-opted-out-' + this.model.entityType + '-' + this.name;
        },

        setup: function () {
            this.seeds = {};

            let linkList = this.getMetadata().get(['scopes', 'TargetList', 'targetLinkList']) || [];

            this.scopeList = [];

            linkList.forEach(link => {
                let entityType = this.getMetadata().get(['entityDefs', 'TargetList', 'links', link, 'entity']);

                if (entityType) {
                    this.scopeList.push(entityType);
                }
            });

            this.listLayout = {};

            this.scopeList.forEach(scope => {
                this.listLayout[scope] = {
                    rows: [
                        [
                            {
                                name: 'name',
                                link: true,
                            }
                        ]
                    ]
                };
            });

            if (this.scopeList.length) {
                this.wait(true);

                var i = 0;

                this.scopeList.forEach(scope => {
                    this.getModelFactory().create(scope, seed => {
                        this.seeds[scope] = seed;

                        i++;

                        if (i === this.scopeList.length) {
                            this.wait(false);
                        }
                    });
                });
            }

            this.listenTo(this.model, 'opt-out', () => {
                this.actionRefresh();
            });

            this.listenTo(this.model, 'cancel-opt-out', () => {
                this.actionRefresh();
            });
        },

        afterRender: function () {
            var url = 'TargetList/' + this.model.id + '/' + this.name;

            this.collection = new MultiCollection();
            this.collection.seeds = this.seeds;
            this.collection.url = url;

            this.collection.maxSize = this.getConfig().get('recordsPerPageSmall') || 5;

            this.listenToOnce(this.collection, 'sync', () => {
                this.createView('list', 'views/record/list-expanded', {
                    selector: '> .list-container',
                    pagination: false,
                    type: 'listRelationship',
                    rowActionsView: 'crm:views/target-list/record/row-actions/opted-out',
                    checkboxes: false,
                    collection: this.collection,
                    multiListLayout: this.listLayout,
                }, view => {
                    view.render();
                });
            });

            this.collection.fetch();
        },

        actionRefresh: function () {
            this.collection.fetch();
        },

        actionCancelOptOut: function (data) {
            this.confirm(this.translate('confirmation', 'messages'), () => {
                Espo.Ajax.postRequest('TargetList/action/cancelOptOut', {
                    id: this.model.id,
                    targetId: data.id,
                    targetType: data.type,
                }).then(() => {
                    this.collection.fetch();
                });
            });
        },
    });
});
