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
import SelectRelatedHelper from 'helpers/record/select-related';

class FollowersListModalView extends RelatedListModalView {

    massActionRemoveDisabled = true
    massActionMassUpdateDisabled = true
    mandatorySelectAttributeList = ['type']

    setup() {
        if (
            !this.getUser().isAdmin() &&
            this.getAcl().getPermissionLevel('followerManagementPermission') === 'no' &&
            this.getAcl().getPermissionLevel('portalPermission') === 'no'
        ) {
            this.unlinkDisabled = true;
        }

        super.setup();
    }

    actionSelectRelated() {
        let filter = 'active';

        if (
            !this.getUser().isAdmin() &&
            this.getAcl().getPermissionLevel('followerManagementPermission') === 'no' &&
            this.getAcl().getPermissionLevel('portalPermission') === 'yes'
        ) {
            filter = 'activePortal';
        }

        const helper = new SelectRelatedHelper(this);

        helper.process(this.model, this.link, {
            primaryFilterName: filter,
            massSelect: false,
            foreignEntityType: 'User',
            viewKey: 'selectFollowers',
        });
    }
}

export default FollowersListModalView;
