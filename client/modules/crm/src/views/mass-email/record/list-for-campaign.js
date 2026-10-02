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

import ListRecordView from 'views/record/list';
import MassEmailSendTestModalView from 'crm:views/mass-email/modals/send-test';

export default class extends ListRecordView {

    // noinspection JSUnusedGlobalSymbols
    async actionSendTest(data) {
        const id = data.id;

        const model = this.collection.get(id);

        if (!model) {
            return;
        }

        const view = new MassEmailSendTestModalView({model: model});

        await this.assignView('modal', view);
        await view.render();
    }
}
