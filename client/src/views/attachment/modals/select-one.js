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

import ModalView from 'views/modal';

export default class SelectOneAttachmentModalView extends ModalView {

    backdrop = true

    // language=Handlebars
    templateContent =
        `<ul class="list-group no-side-margin">
            {{#each viewObject.options.dataList}}
                <li
                    class="list-group-item"
                ><a
                    role="button"
                    class="action"
                    data-action="select"
                    data-id="{{id}}"
                >{{name}}</a></li>
            {{/each}}
        </ul>
        `

    /**
     *
     * @param {{
     *     fieldLabel?: string,
     *     dataList: {id: string, name: string}[],
     *     onSelect: function(string),
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        this.headerText = this.translate('Select');

        if (this.options.fieldLabel) {
            this.headerText += ' · ' + this.options.fieldLabel;
        }

        this.addActionHandler('select', (e, target) => {
            this.options.onSelect(target.dataset.id);

            this.close();
        });
    }
}
