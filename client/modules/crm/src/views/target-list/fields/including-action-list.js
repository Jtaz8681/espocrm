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

define('crm:views/target-list/fields/including-action-list', ['views/fields/multi-enum'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            this.params.options = this.getMetadata().get('entityDefs.CampaignLogRecord.fields.action.options') || [];
            this.translatedOptions = {};

            this.params.options.forEach(item => {
                this.translatedOptions[item] = this.getLanguage().translateOption(item, 'action', 'CampaignLogRecord');
            });
        },
    });
});
