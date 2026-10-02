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

class EmailDefaultRowActionView extends DefaultRowActionsView {

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:isImportant change:inTrash change:groupStatusFolder', () => {
            setTimeout(() => this.reRender(), 10);
        });
    }

    getActionList() {
        /** @type {import('views/record/list').RowAction[]} */
        let list = [{
            action: 'quickView',
            label: 'View',
            data: {
                id: this.model.id
            },
            groupIndex: 0,
            iconClass: DefaultRowActionsView.ICON_CLASS_VIEW,
        }];

        if (
            this.model.get('createdById') === this.getUser().id && this.model.get('status') === 'Draft' &&
            !this.model.attributes.inTrash
        ) {
            list.push({
                action: 'send',
                label: 'Send',
                data: {
                    id: this.model.id,
                },
                iconClass: 'far fa-paper-plane',
            });
        }

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
                },
            ]);
        }

        if (this.model.get('isUsers')) {
            if (!this.model.get('isImportant')) {
                if (!this.model.get('inTrash')) {
                    list.push({
                        action: 'markAsImportant',
                        label: 'Mark as Important',
                        data: {
                            id: this.model.id
                        },
                        groupIndex: 1,
                        iconClass: 'far fa-star',
                    });
                }
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

        if (this.model.attributes.isUsers && !this.model.attributes.isRead) {
            list.push({
                action: 'markAsRead',
                label: 'Mark Read',
                data: {
                    id: this.model.id
                },
                groupIndex: 1,
            });
        }

        if (
            (this.model.attributes.isUsers && this.model.attributes.status !== 'Draft') ||
            this.model.attributes.groupFolderId
        ) {
            const inTrash = this.model.attributes.groupFolderId ?
                this.model.attributes.groupStatusFolder === 'Trash' :
                this.model.attributes.inTrash;

            const inArchive = this.model.attributes.groupFolderId ?
                this.model.attributes.groupStatusFolder === 'Archive' :
                this.model.attributes.inArchive;

            if (!inTrash) {
                list.push({
                    action: 'moveToTrash',
                    label: 'Move to Trash',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 2,
                    iconClass: 'far fa-trash-alt',
                });
            } else {
                list.push({
                    action: 'retrieveFromTrash',
                    label: 'Retrieve from Trash',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 2,
                });
            }

            if (!inArchive) {
                list.push({
                    action: 'moveToArchive',
                    text: this.getLanguage().translatePath('Email.actions.moveToArchive'),
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 2,
                    iconClass: 'far fa-caret-square-down',
                });
            }

            list.push({
                action: 'moveToFolder',
                label: 'Move to Folder',
                data: {
                    id: this.model.id
                },
                groupIndex: 2,
                iconClass: 'far fa-folder',
            });
        } else if (
            !this.model.attributes.isUsers &&
            !this.model.attributes.groupFolderId &&
            this.model.attributes.status === 'Archived'
        ) {
            list.push({
                action: 'moveToFolder',
                label: 'Move to Folder',
                data: {
                    id: this.model.id
                },
                groupIndex: 2,
                iconClass: 'far fa-folder',
            });
        }

        if (this.options.acl.delete) {
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


        return list;
    }
}

export default EmailDefaultRowActionView;
