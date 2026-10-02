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

    templateContent = `<div class="record no-side-margin">{{{record}}}</div>`

    setup() {
        super.setup();

        this.headerText = this.translate('attachments', 'fields', 'Email');

        this.createView('record', 'views/record/detail', {
            model: this.model,
            selector: '.record',
            readOnly: true,
            sideView: null,
            buttonsDisabled: true,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                name: 'attachments',
                                noLabel: true,
                            },
                            false,
                        ]
                    ]
                }
            ],
        });

        if (!this.model.has('attachmentsIds')) {
            this.wait(
                this.model.fetch()
            );
        }
    }
}
