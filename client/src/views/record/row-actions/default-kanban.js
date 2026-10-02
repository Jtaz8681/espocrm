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

import DefaultRowActionsView from 'views/record/row-actions/default';

class DefaultKanbanRowActionsView extends DefaultRowActionsView {

    getActionList() {
        const list = [{
            action: 'quickView',
            label: 'View',
            data: {
                id: this.model.id,
            },
            link: '#' + this.model.entityType + '/view/' + this.model.id,
            groupIndex: 0,
            iconClass: DefaultRowActionsView.ICON_CLASS_VIEW,
        }];

        if (this.options.moveOverRowAction && this.options.statusFieldIsEditable) {
            list.push({
                action: 'moveOver',
                label: 'Move Over',
                data: {
                    id: this.model.id,
                },
                groupIndex: 1,
            });
        }

        if (this.options.acl.edit) {
            list.push({
                action: 'quickEdit',
                label: 'Edit',
                data: {
                    id: this.model.id
                },
                link: '#' + this.model.entityType + '/edit/' + this.model.id,
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_EDIT,
            });
        }

        this.getAdditionalActionList().forEach(item => list.push(item));

        if (this.options.acl.delete) {
            list.push({
                action: 'quickRemove',
                label: 'Remove',
                data: {
                    id: this.model.id,
                },
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_REMOVE,
            });
        }

        return list;
    }
}

export default DefaultKanbanRowActionsView;
