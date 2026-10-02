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

class IframeDashletView extends BaseDashletView {

    name = 'Iframe'

    /**
     * @private
     * @type {boolean}
     */
    sandboxDisabled = false

    // language=Handlebars
    templateContent = `
        <iframe
            style="margin: 0; border: 0;"
            {{#unless viewObject.sandboxDisabled}}
                sandbox="allow-scripts"
            {{/unless}}
        ></iframe>
    `

    setup() {
        const url = this.getOption('url');

        /** @type {string[]} */
        const excludeDomains = this.getConfig().get('iframeSandboxExcludeDomainList') || [];

        if (url) {
            for (const domain of excludeDomains) {
                try {
                    const urlObject = new URL(url);

                    if (urlObject.hostname === domain) {
                        this.sandboxDisabled = true;

                        break;
                    }
                } catch (e) {
                    console.warn(`Invalid URL ${url}.`);
                }
            }
        }
    }

    afterRender() {
        const $iframe = this.$el.find('iframe');

        const url = this.getOption('url');

        if (url) {
            $iframe.attr('src', url);
        }

        this.$el.addClass('no-padding');
        this.$el.css('overflow', 'hidden');

        const height = this.$el.height();

        $iframe.css('height', height);
        $iframe.css('width', '100%');
    }

    afterAdding() {
        this.getContainerView().actionOptions();
    }
}

export default IframeDashletView;
