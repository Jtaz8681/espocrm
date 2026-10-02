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

export default class extends DefaultRowActionsView {

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:isImportant', () => {
            setTimeout(() => this.reRender(), 10);
        });
    }

    getActionList() {
        let list = [{
            action: 'quickView',
            label: 'View',
            data: {
                id: this.model.id
            },
            groupIndex: 0,
            iconClass: DefaultRowActionsView.ICON_CLASS_VIEW,
        }];

        if (this.options.acl.edit) {
            list = list.concat([
                {
                    action: 'quickEdit',
                    label: 'Edit',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 0,
                    iconClass: DefaultRowActionsView.ICON_CLASS_EDIT,
                }
            ]);
        }

        if (this.model.get('isUsers') && this.model.get('status') !== 'Draft') {
            if (!this.model.get('inTrash')) {
                list.push({
                    action: 'moveToTrash',
                    label: 'Move to Trash',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 1,
                    iconClass: 'far fa-trash-alt'
                });
            } else {
                list.push({
                    action: 'retrieveFromTrash',
                    label: 'Retrieve from Trash',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 1,
                });
            }
        }

        if (this.getAcl().checkModel(this.model, 'delete')) {
            list.push({
                action: 'quickRemove',
                label: 'Remove',
                data: {
                    id: this.model.id
                },
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_REMOVE,
            });
        }

        if (this.model.get('isUsers')) {
            if (!this.model.get('isImportant')) {
                list.push({
                    action: 'markAsImportant',
                    label: 'Mark as Important',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 1,
                    iconClass: 'far fa-star',
                });
            } else {
                list.push({
                    action: 'markAsNotImportant',
                    label: 'Unmark Importance',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 1,
                });
            }
        }

        return list;
    }
}
