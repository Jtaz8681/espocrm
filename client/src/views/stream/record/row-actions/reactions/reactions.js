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

import View from 'view';

export default class ReactionsRowActionView extends View {

    // language=Handlebars
    templateContent = `
        <div class="item-icon-grid">
            {{#each reactions}}
                <a
                    role="button"
                    {{#if isReacted}}
                        data-action="unReact"
                    {{else}}
                        data-action="react"
                    {{/if}}
                    data-type="{{type}}"
                    title="{{label}}"
                    class=" {{#if isReacted}} text-primary {{else}} text-soft {{/if}}"
                ><span class="{{iconClass}}"></span></a>
            {{/each}}
        </div>
    `
    /**
     * @param {{
     *     reactions: {
     *         type: string,
     *         iconClass: string|null,
     *         label: string,
     *         isReacted: boolean,
     *     }[]
     * }} options
     */
    constructor(options) {
        super(options);

        this.reactions = options.reactions;
    }

    data() {
        return {
            reactions: this.reactions,
        };
    }
}
