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

import DetailView from 'views/detail';
import MassEmailSendTestModalView from 'crm:views/mass-email/modals/send-test';

export default class extends DetailView {

    setup() {
        super.setup();

        if (
            ['Draft', 'Pending'].includes(this.model.attributes.status) &&
            this.getAcl().checkModel(this.model, 'edit')
        ) {
            this.addMenuItem('buttons', {
                label: 'Send Test',
                action: 'sendTest',
                acl: 'edit',
                onClick: () => this.actionSendTest(),
            });
        }
    }

    async actionSendTest() {
        const view = new MassEmailSendTestModalView({model: this.model});

        await this.assignView('modal', view);
        await view.render();
    }
}
