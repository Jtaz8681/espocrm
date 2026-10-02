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

define('crm:views/target-list/record/row-actions/default', ['views/record/row-actions/relationship'], function (Dep) {

    return Dep.extend({

        getActionList: function () {
            const list = Dep.prototype.getActionList.call(this);

            if (
                this.model.collection &&
                this.model.collection.parentModel &&
                this.getAcl().checkModel(this.model.collection.parentModel, 'edit')
            ) {
                if (this.model.get('targetListIsOptedOut')) {
                    list.push({
                        action: 'cancelOptOut',
                        text: this.translate('Cancel Opt-Out', 'labels', 'TargetList'),
                        data: {
                            id: this.model.id,
                            type: this.model.entityType,
                        },
                    });
                } else {
                    list.push({
                        action: 'optOut',
                        text: this.translate('Opt-Out', 'labels', 'TargetList'),
                        data: {
                            id: this.model.id,
                            type: this.model.entityType,
                        },
                    });
                }
            }

            return list;
        },
    });
});
