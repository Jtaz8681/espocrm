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

import RelationshipRowActionsView from 'views/record/row-actions/relationship';

// noinspection JSUnusedGlobalSymbols
export default class UserRelationshipFollowersRowActionsView extends RelationshipRowActionsView {

    getActionList() {
        const list = [];

        const model = /** @type {import('models/user').default} */this.model;

        list.push({
            action: 'quickView',
            label: 'View',
            data: {
                id: this.model.id,
            },
            link: `#${this.model.entityType}/view/${this.model.id}`
        })

        if (
            this.getUser().isAdmin() ||
            this.getAcl().getPermissionLevel('followerManagementPermission') !== 'no' ||
            model.isPortal() && this.getAcl().getPermissionLevel('portalPermission') === 'yes' ||
            this.model.id === this.getUser().id
        ) {
            list.push({
                action: 'unlinkRelated',
                label: 'Unlink',
                data: {
                    id: this.model.id,
                },
            });
        }

        return list;
    }
}
