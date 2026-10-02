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

define('crm:views/record/row-actions/relationship-target', ['views/record/row-actions/relationship-unlink-only'], function (Dep) {

    return Dep.extend({

        getActionList: function () {
            var list = Dep.prototype.getActionList.call(this);

            if (this.options.acl.edit) {
                if (this.model.get('isOptedOut')) {
                    list.push({
                        action: 'cancelOptOut',
                        text: this.translate('Cancel Opt-Out', 'labels', 'TargetList'),
                        data: {
                            id: this.model.id
                        },
                        groupIndex: 1,
                    });
                } else {
                    list.push({
                        action: 'optOut',
                        text: this.translate('Opt-Out', 'labels', 'TargetList'),
                        data: {
                            id: this.model.id
                        },
                        groupIndex: 1,
                    });
                }
            }

            return list;
        },
    });
});
