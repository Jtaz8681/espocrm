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

import RelatedListModalView from 'views/modals/related-list';

export default class {

    toShow

    constructor(/** import('views/record/detail').default */view) {
        this.view = view;
        this.metadata = /** @type {module:metadata} */view.getMetadata();
        this.entityType = this.view.entityType;
        this.model = this.view.model;

        const level = this.view.getAcl().getPermissionLevel('user');

        this.toShow = (level === 'all' || level === 'team') &&
            (
                this.metadata.get(`scopes.${this.entityType}.object`) ||
                this.metadata.get(`scopes.${this.entityType}.acl`)
            )
            this.view.getAcl().checkScope('User');
    }

    isAvailable() {
        return this.toShow;
    }

    async show() {
        const actionList = this.getActionList();

        /** @type {Record[]} */
        const listLayout = [
            {
                name: 'name',
                link: true,
                view: 'views/user/fields/name',
            },
        ];

        //const width = Math.round((100.0 - 40) / (actionList.length + 1));

        actionList.forEach(action => {
            listLayout.push({
                name: 'recordAccessLevel' + action,
                customLabel: this.view.translate(action, 'recordActions'),
                view: 'views/user/fields/record-access-level',
                notSortable: true,
                width: 16,
            });
        });

        const view = new RelatedListModalView({
            model: this.model,
            link: 'usersAccess',
            entityType: 'User',
            title: this.view.translate('View User Access'),
            url: `${this.entityType}/${this.model.id}/usersAccess`,
            createDisabled: true,
            selectDisabled: true,
            massActionsDisabled: true,
            maxSize: this.view.getConfig().get('recordsPerPageSmall'),
            rowActionsView: null,
            listLayout: listLayout,
            filter: 'active',
        });

        await this.view.assignView('dialog', view);
        await view.render();
    }

    /**
     * @private
     * @return {string[]}
     */
    getActionList() {
        /** @type {string[]} */
        let actionList = this.metadata.get(`scopes.${this.entityType}.aclActionList`);

        if (!actionList) {
            actionList = [
                'read',
                'edit',
                'delete',
            ];

            if (this.metadata.get(`scopes.${this.entityType}.stream`)) {
                actionList.push('stream');
            }
        }

        return actionList.filter(it => it !== 'create');
    }
}
