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

export default class TextPreviewModalView extends ModalView {

    // language=Handlebars
    templateContent = `
        <div class="panel panel-default no-side-margin">
            <div class="panel-body">
                <div class="complex-text">{{complexText viewObject.options.text linksInNewTab=true}}</div>
            </div>
        </div>
    `

    backdrop = true


    /**
     * @param {{text: string}} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        this.headerText = this.translate('Preview');
    }
}
