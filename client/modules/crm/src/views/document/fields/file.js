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

define('crm:views/document/fields/file', ['views/fields/file'], function (Dep) {

    return Dep.extend({

        getValueForDisplay: function () {
            if (this.isListMode()) {
                let name = this.model.get(this.nameName);
                let id = this.model.get(this.idName);

                if (!id) {
                    return '';
                }

                return $('<a>')
                    .attr('title', name)
                    .attr('href', this.getBasePath() + '?entryPoint=download&id=' + id)
                    .attr('target', '_BLANK')
                    .append(
                        $('<span>').addClass('fas fa-paperclip small')
                    )
                    .get(0).outerHTML;
            }

            return Dep.prototype.getValueForDisplay.call(this);
        },
    });
});
