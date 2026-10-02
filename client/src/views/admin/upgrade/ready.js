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

    template = 'admin/upgrade/ready'

    cssName = 'ready-modal'
    createButton = true

    data() {
        return {
            version: this.upgradeData.version,
            text: this.translate('upgradeVersion', 'messages', 'Admin')
                .replace('{version}', this.upgradeData.version)
        };
    }

    setup() {
        this.buttonList = [
            {
                name: 'run',
                label: this.translate('Run Upgrade', 'labels', 'Admin'),
                style: 'danger',
                onClick: () => this.actionRun(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ]

        this.upgradeData = this.options.upgradeData;

        this.headerText = this.getLanguage().translate('Ready for upgrade', 'labels', 'Admin');
    }

    actionRun() {
        this.trigger('run');
        this.remove();
    }
}
