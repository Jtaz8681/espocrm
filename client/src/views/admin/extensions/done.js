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

    template = 'admin/extensions/done'
    cssName = 'done-modal'
    createButton = true

    data() {
        return {
            version: this.options.version,
            name: this.options.name,
            text: this.translate('extensionInstalled', 'messages', 'Admin')
                .replace('{version}', this.options.version)
                .replace('{name}', this.options.name)
        };
    }

    setup() {
        this.on('remove', () => {
            window.location.reload();
        });

        this.buttonList = [
            {
                name: 'close',
                label: 'Close',
            }
        ];

        this.headerText = this.getLanguage().translate('Installed successfully', 'labels', 'Admin');
    }
}
