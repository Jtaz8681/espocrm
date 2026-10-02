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
import Ui from 'ui';

export default class extends ListRecordView {

    rowActionsView = 'crm:views/task/record/row-actions/default'

    actionSetCompleted(data) {
        const id = data.id;

        if (!id) {
            return;
        }

        const model = this.collection.get(id);

        if (!model) {
            return;
        }

        /** @var string[]*/
        const completedStatusList = this.getMetadata().get(`scopes.Task.completedStatusList`, []);

        const status = completedStatusList[0] ?? null;

        Ui.notify(this.translate('saving', 'messages'));

        model.save({status: status}, {patch: true}).then(() => {
            Ui.success(this.translate('Saved'));

            this.collection.fetch();
        });
    }
}
