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

define('crm:views/dashlets/options/chart', ['views/dashlets/options/base'], function (Dep) {

    return Dep.extend({

        setupBeforeFinal: function () {
            this.listenTo(this.model, 'change:dateFilter', this.controlDateFilter);
            this.controlDateFilter();
        },

        controlDateFilter: function () {
            if (this.model.get('dateFilter') === 'between') {
                this.showField('dateFrom');
                this.showField('dateTo');
            } else {
                this.hideField('dateFrom');
                this.hideField('dateTo');
            }
        },
    });
});
