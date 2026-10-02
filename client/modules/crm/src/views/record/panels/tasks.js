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

import RelationshipPanelView from 'views/record/panels/relationship';
import CreateRelatedHelper from 'helpers/record/create-related';

export default class TasksRelationshipPanelView extends RelationshipPanelView {

    name = 'tasks'
    entityType = 'Task'
    filterList = ['all', 'actual', 'completed']

    orderBy = 'createdAt'
    orderDirection = 'desc'

    rowActionsView = 'crm:views/record/row-actions/tasks'

    buttonList = [
        {
            action: 'createTask',
            title: 'Create Task',
            acl: 'create',
            aclScope: 'Task',
            html: '<span class="fas fa-plus"></span>',
        },
    ]

    actionList = [
        {
            label: 'View List',
            action: 'viewRelatedList',
            iconClass: 'fas fa-align-justify',
        }
    ]

    listLayout = {
        rows: [
            [
                {
                    name: 'name',
                    link: true,
                },
            ],
            [
                {
                    name: 'isOverdue'
                },
                {name: 'assignedUser'},
                {
                    name: 'dateEnd',
                    soft: true
                },
                {name: 'status'},
            ]
        ]
    }

    setup() {
        this.parentScope = this.model.entityType;
        this.link = 'tasks';

        this.panelName = 'tasksSide';

        this.defs.create = true;

        if (this.parentScope === 'Account') {
            this.link = 'tasksPrimary';
        }

        this.url = this.model.entityType + '/' + this.model.id + '/' + this.link;

        this.setupListLayout();
        this.setupSorting();

        if (this.filterList && this.filterList.length) {
            this.filter = this.getStoredFilter();
        }

        this.setupFilterActions();

        this.setupTitle();

        this.wait(true);

        this.getCollectionFactory().create('Task', (collection) => {
            this.collection = collection;
            collection.seeds = this.seeds;
            collection.url = this.url;
            collection.orderBy = this.defaultOrderBy;
            collection.order = this.defaultOrder;
            collection.maxSize = this.getConfig().get('recordsPerPageSmall') || 5;

            this.setFilter(this.filter);
            this.wait(false);
        });

        this.once('show', () => {
            if (!this.isRendered() && !this.isBeingRendered()) {
                this.collection.fetch();
            }
        });

        let events = `update-related:${this.link} update-all`;

        if (this.parentScope === 'Account') {
            events += ' update-related:tasks';
        }

        this.listenTo(this.model, events, () => this.collection.fetch());
    }

    /**
     * @private
     */
    setupListLayout() {
        if (!this.getMetadata().get(`scopes.Task.assignedUsers`)) {
            return;
        }

        this.listLayout = Espo.Utils.cloneDeep(this.listLayout);

        for (const row of this.listLayout.rows) {
            const index = row.findIndex(row => row.name === 'assignedUser');

            if (index !== -1) {
                row.splice(index, 1);
            }
        }

        this.listLayout.rows.push([
            {
                name: 'assignedUsers',
            }
        ]);
    }

    afterRender() {
        this.createView('list', 'views/record/list-expanded', {
            selector: '> .list-container',
            pagination: false,
            type: 'listRelationship',
            rowActionsView: this.defs.rowActionsView || this.rowActionsView,
            checkboxes: false,
            collection: this.collection,
            listLayout: this.listLayout,
            skipBuildRows: true,
        }, (view) => {
            view.getSelectAttributeList(selectAttributeList => {
                if (selectAttributeList) {
                    this.collection.data.select = selectAttributeList.join(',');
                }

                if (!this.disabled) {
                    this.collection.fetch();

                    return;
                }

                this.once('show', () => this.collection.fetch());
            });
        });
    }

    actionCreateRelated() {
        this.actionCreateTask();
    }

    actionCreateTask() {
        let link = this.link;

        if (this.parentScope === 'Account') {
            link = 'tasks';
        }

        const helper = new CreateRelatedHelper(this);

        helper.process(this.model, link)
    }

    // noinspection JSUnusedGlobalSymbols
    actionComplete(data) {
        const id = data.id;

        if (!id) {
            return;
        }

        const model = this.collection.get(id);

        model.save({status: 'Completed'}, {patch: true})
            .then(() => this.collection.fetch());
    }

    actionViewRelatedList(data) {
        data.viewOptions = data.viewOptions || {};
        data.viewOptions.massUnlinkDisabled = true;

        super.actionViewRelatedList(data);
    }
}
