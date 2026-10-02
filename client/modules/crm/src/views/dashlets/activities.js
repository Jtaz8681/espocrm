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
import MultiCollection from 'multi-collection';
import RecordModalHelper from 'helpers/record-modal';

class ActivitiesDashletView extends BaseDashletView {

    name = 'Activities'

    // language=Handlebars
    templateContent = '<div class="list-container">{{{list}}}</div>'

    rowActionsView = 'crm:views/record/row-actions/activities-dashlet'

    defaultListLayout = {
        rows: [
            [
                {
                    name: 'ico',
                    view: 'crm:views/fields/ico',
                    params: {
                        notRelationship: true,
                    },
                },
                {
                    name: 'name',
                    link: true,
                },
            ],
            [
                {
                    name: 'dateStart',
                    soft: true
                },
                {
                    name: 'parent',
                },
            ],
        ],
    }

    listLayoutEntityTypeMap = {
        Task: {
            rows: [
                [
                    {
                        name: 'ico',
                        view: 'crm:views/fields/ico',
                        params: {
                            notRelationship: true
                        },
                    },
                    {
                        name: 'name',
                        link: true,
                    },
                ],
                [
                    {
                        name: 'status',
                    },
                    {
                        name: 'dateEnd',
                        soft: true
                    },
                    {
                        name: 'priority',
                        view: 'crm:views/task/fields/priority-for-dashlet',
                    },
                    {
                        name: 'parent',
                    },
                ],
            ]
        }
    }

    setup() {
        this.seeds = {};

        this.scopeList = this.getOption('enabledScopeList') || [];

        this.listLayout = {};

        this.scopeList.forEach((item) => {
            if (item in this.listLayoutEntityTypeMap) {
                this.listLayout[item] = this.listLayoutEntityTypeMap[item];

                return;
            }

            this.listLayout[item] = this.defaultListLayout;
        });

        this.wait(true);
        let i = 0;

        this.scopeList.forEach(scope => {
            this.getModelFactory().create(scope, seed => {
                this.seeds[scope] = seed;

                i++;

                if (i === this.scopeList.length) {
                    this.wait(false);
                }
            });
        });

        this.scopeList.slice(0).reverse().forEach(scope => {
            if (this.getAcl().checkScope(scope, 'create')) {
                this.actionList.unshift({
                    name: 'createActivity',
                    text: this.translate('Create ' + scope, 'labels', scope),
                    iconClass: 'fas fa-plus',
                    url: '#' + scope + '/create',
                    data: {
                        scope: scope,
                    },
                });
            }
        });
    }

    afterRender() {
        this.collection = new MultiCollection();
        this.collection.seeds = this.seeds;
        this.collection.url = 'Activities/upcoming';
        this.collection.maxSize = this.getOption('displayRecords') ||
            this.getConfig().get('recordsPerPageSmall') || 5;
        this.collection.data.entityTypeList = this.scopeList;
        this.collection.data.futureDays = this.getOption('futureDays');

        if (this.getOption('includeShared')) {
            this.collection.data.includeShared = true;
        }

        this.listenToOnce(this.collection, 'sync', () => {
            this.createView('list', 'crm:views/record/list-activities-dashlet', {
                selector: '> .list-container',
                pagination: false,
                type: 'list',
                rowActionsView: this.rowActionsView,
                checkboxes: false,
                collection: this.collection,
                multiListLayout: this.listLayout,
            }, view => {
                view.render();
            });
        });

        this.collection.fetch();
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
        if (!options.skipNotify) {
            Espo.Ui.notifyWait();
        }

        await this.collection.fetch({
            previousTotal: this.collection.total,
            previousDataList: this.collection.models.map(model => {
                return Espo.Utils.cloneDeep(model.attributes);
            }),
        });

        if (!options.skipNotify) {
            Espo.Ui.notify();
        }
    }

    // noinspection JSUnusedGlobalSymbols
    actionCreateActivity(data) {
        const scope = data.scope;
        const attributes = {};

        this.populateAttributesAssignedUser(scope, attributes);

        const helper = new RecordModalHelper();

        helper.showCreate(this, {
            entityType: scope,
            attributes: attributes,
            afterSave: () => {
                this.actionRefresh();
            },
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionCreateMeeting() {
        const attributes = {};

        this.populateAttributesAssignedUser('Meeting', attributes);

        const helper = new RecordModalHelper();

        helper.showCreate(this, {
            entityType: 'Meeting',
            attributes: attributes,
            afterSave: () => {
                this.actionRefresh();
            },
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionCreateCall() {
        const attributes = {};

        this.populateAttributesAssignedUser('Call', attributes);

        const helper = new RecordModalHelper();

        helper.showCreate(this, {
            entityType: 'Call',
            attributes: attributes,
            afterSave: () => {
                this.actionRefresh();
            },
        });
    }

    populateAttributesAssignedUser(scope, attributes) {
        if (this.getMetadata().get(['entityDefs', scope, 'fields', 'assignedUsers'])) {
            attributes['assignedUsersIds'] = [this.getUser().id];
            attributes['assignedUsersNames'] = {};
            attributes['assignedUsersNames'][this.getUser().id] = this.getUser().get('name');
        } else {
            attributes['assignedUserId'] = this.getUser().id;
            attributes['assignedUserName'] = this.getUser().get('name');
        }
    }
}

export default ActivitiesDashletView;
