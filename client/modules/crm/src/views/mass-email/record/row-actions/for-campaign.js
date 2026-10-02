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

define('crm:views/mass-email/record/row-actions/for-campaign',
['views/record/row-actions/relationship-no-unlink'], function (Dep) {

    return Dep.extend({

        getActionList: function () {
            var actionList = Dep.prototype.getActionList.call(this);

            if (this.options.acl.edit && !~['Complete'].indexOf(this.model.get('status'))) {
                actionList.unshift({
                    action: 'sendTest',
                    label: 'Send Test',
                    data: {
                        id: this.model.id,
                    }
                });
            }

            return actionList;
        }
    });
});
