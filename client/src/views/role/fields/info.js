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

import BaseFieldView from 'views/fields/base';

export default class extends BaseFieldView {

    // language=Handlebars
    listTemplateContent = ``

    /**
     * @private
     * @type {string|null}
     */
    baselineRoleId

    setup() {
        super.setup();

        this.baselineRoleId = this.getConfig().get('baselineRoleId');
    }

    afterRenderList() {
        super.afterRenderList();

        if (this.baselineRoleId && this.model.id === this.baselineRoleId) {
            this.element?.append(
                (() => {
                    const span = document.createElement('span');

                    span.className = 'label label-default';
                    span.textContent = this.translate('Baseline', 'labels', 'Role');

                    return span;
                })()
            )
        }
    }
}
