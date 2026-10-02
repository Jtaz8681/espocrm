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

    cssName = 'select-folder-modal'

    template = 'email-account/modals/select-folder'

    data() {
        return {
            folders: this.options.folders,
        };
    }

    setup() {
        this.headerText = this.translate('Select');

        this.addActionHandler('select', (event, target) => {
            const value = target.dataset.value;

            this.trigger('select', value);
        });
    }
}
