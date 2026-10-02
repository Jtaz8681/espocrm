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

import RelationshipActionsView from 'views/record/row-actions/relationship';
import RelationshipRowActionsView from 'views/record/row-actions/relationship';

class RelationshipRemoveOnlyActionsView extends RelationshipActionsView {

    getActionList() {
        if (this.options.acl.delete) {
            return [
                {
                    action: 'removeRelated',
                    label: 'Remove',
                    data: {
                        id: this.model.id,
                    },
                    groupIndex: 0,
                    iconClass: RelationshipRowActionsView.ICON_CLASS_REMOVE,
                },
            ];
        }

        return [];
    }
}

// noinspection JSUnusedGlobalSymbols
export default RelationshipRemoveOnlyActionsView;
