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

define('crm:views/mass-email/record/detail-bottom', ['views/record/detail-bottom'], function (Dep) {

    return Dep.extend({

        setupPanels: function () {
            Dep.prototype.setupPanels.call(this);

            this.panelList.unshift({
                name: 'queueItems',
                label: this.translate('queueItems', 'links', 'MassEmail'),
                view: 'views/record/panels/relationship',
                select: false,
                create: false,
                layout: 'listForMassEmail',
                rowActionsView: 'views/record/row-actions/empty',
                filterList: ['all', 'pending', 'sent', 'failed'],
            });
        },

        afterRender: function () {
            Dep.prototype.setupPanels.call(this);
        },
    });
});
