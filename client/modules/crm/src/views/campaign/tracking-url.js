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

export default class extends View {

    // language=Handlebars
    templateContent = `
        <div class="container content">
            <div class="block-center-md">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="complex-text">{{complexText message}}</div>
                    </div>
                </div>
            </div>
        </div>
    `

    data() {
        // noinspection JSUnresolvedReference
        return {
            message: this.options.message,
        };
    }
}
