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

define('crm:views/record/row-actions/tasks', ['views/record/row-actions/relationship-no-unlink'], function (Dep) {

    return Dep.extend({

        getActionList: function () {
            var list = [{
                action: 'quickView',
                label: 'View',
                data: {
                    id: this.model.id
                },
                link: '#' + this.model.entityType + '/view/' + this.model.id,
                groupIndex: 0,
                iconClass: Dep.ICON_CLASS_VIEW,
            }];

            if (this.options.acl.edit) {
                list.push({
                    action: 'quickEdit',
                    label: 'Edit',
                    data: {
                        id: this.model.id
                    },
                    link: '#' + this.model.entityType + '/edit/' + this.model.id,
                    groupIndex: 0,
                    iconClass: Dep.ICON_CLASS_EDIT,
                });

                // @todo Refactor.
                if (!['Completed', 'Canceled'].includes(this.model.get('status'))) {
                    list.push({
                        action: 'Complete',
                        text: this.translate('Complete', 'labels', 'Task'),
                        data: {
                            id: this.model.id
                        },
                        groupIndex: 1,
                        iconClass: 'fas fa-check',
                    });
                }
            }

            if (this.options.acl.delete) {
                list.push({
                    action: 'removeRelated',
                    label: 'Remove',
                    data: {
                        id: this.model.id
                    },
                    groupIndex: 0,
                    iconClass: Dep.ICON_CLASS_REMOVE,
                });
            }

            return list;
        },
    });
});
