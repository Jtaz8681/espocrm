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

class RelationshipViewAndUnlinkActionsView extends RelationshipRowActionsView {

    getActionList() {
        const list = [{
            action: 'quickView',
            label: 'View',
            data: {
                id: this.model.id,
            },
            link: '#' + this.model.entityType + '/view/' + this.model.id,
            groupIndex: 0,
            iconClass: RelationshipRowActionsView.ICON_CLASS_VIEW,
        }];

        if (this.options.acl.edit && !this.options.unlinkDisabled) {
            list.push({
                action: 'unlinkRelated',
                label: 'Unlink',
                data: {
                    id: this.model.id,
                },
                groupIndex: 1,
            });
        }

        return list;
    }
}

export default RelationshipViewAndUnlinkActionsView;
