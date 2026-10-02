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

import SelectRecordsModalView from 'views/modals/select-records';

export default class SelectForPortalUserModalView extends SelectRecordsModalView {

    /**
     * @param {
     *     module:views/modals/select-records~Options &
     *     {onSkip: function()}
     * } options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        super.setup();

        this.buttonList.unshift({
            name: 'skip',
            text: this.translate('Proceed w/o Contact', 'labels', 'User'),
            onClick: () => this.actionSkip(),
        });
    }

    actionSkip() {
        this.options.onSkip();

        this.close();
    }
}
