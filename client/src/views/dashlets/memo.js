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

import BaseDashletView from 'views/dashlets/abstract/base';

class MemoDashletView extends BaseDashletView {

    name = 'Memo'

    templateContent = `
        {{#if text}}
        <div class="complex-text complex-text-memo">{{complexText text}}</div>
        {{/if}}
    `

    data() {
        return {
            text: this.getOption('text'),
        };
    }

    afterAdding() {
        this.getContainerView().actionOptions();
    }
}

export default MemoDashletView;
