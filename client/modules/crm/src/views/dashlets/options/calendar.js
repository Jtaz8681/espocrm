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

define('crm:views/dashlets/options/calendar', ['views/dashlets/options/base'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.manageFields();
            this.listenTo(this.model, 'change:mode', this.manageFields, this);
        },


        init: function () {
            Dep.prototype.init.call(this);

            this.fields.enabledScopeList.options = this.getConfig().get('calendarEntityList') || [];
        },

        manageFields: function (model, value, o) {
            if (this.model.get('mode') === 'timeline') {
                this.showField('users');
            } else {
                this.hideField('users');
            }

            if (
                this.getAcl().getPermissionLevel('userCalendar') !== 'no'
                &&
                ~['basicWeek', 'month', 'basicDay'].indexOf(this.model.get('mode'))
            ) {
                this.showField('teams');
            } else {
                if (o && o.ui) {
                    this.model.set('teamsIds', []);
                }

                this.hideField('teams');
            }
        },
    });
});
