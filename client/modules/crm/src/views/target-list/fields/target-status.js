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

define('crm:views/target-list/fields/target-status', ['views/fields/base'], function (Dep) {

    return Dep.extend({

        getValueForDisplay: function () {
            if (this.model.get('isOptedOut')) {
                return this.getLanguage().translateOption('Opted Out', 'targetStatus', 'TargetList');
            }

            return this.getLanguage().translateOption('Listed', 'targetStatus', 'TargetList');
        }
    });
});
