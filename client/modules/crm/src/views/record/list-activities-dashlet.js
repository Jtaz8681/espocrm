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

define('crm:views/record/list-activities-dashlet',
['views/record/list-expanded', 'crm:views/meeting/record/list', 'crm:views/task/record/list'],
function (Dep, MeetingList, TaskList) {

    return Dep.extend({

        actionSetHeld: function (data) {
            MeetingList.prototype.actionSetHeld.call(this, data);
        },

        actionSetNotHeld: function (data) {
            MeetingList.prototype.actionSetNotHeld.call(this, data);
        },

        actionSetCompleted: function (data) {
            TaskList.prototype.actionSetCompleted.call(this, data);
        },
    });
});
