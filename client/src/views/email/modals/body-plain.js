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

export default class extends ModalView {

    backdrop = true

    templateContent = `
        <div class="panel no-side-margin">
            <div class="panel-body">
                <div class="field" data-name="body-plain">{{{bodyPlain}}}</div>
            </div>

        </div>
    `

    setup() {
        super.setup();

        this.buttonList.push({
            'name': 'cancel',
            'label': 'Close',
        });

        this.headerText = this.model.get('name');

        this.createView('bodyPlain', 'views/fields/text', {
            selector: '.field[data-name="bodyPlain"]',
            model: this.model,
            defs: {
                name: 'bodyPlain',
                params: {
                    readOnly: true,
                    inlineEditDisabled: true,
                    displayRawText: true,
                },
            },
        });
    }
}
