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

export default class extends ListRecordView {

    massUpdateDisabled = true
    mergeDisabled = true
    exportDisabled = true
    removeDisabled = true

    rowActionsView = 'views/email-folder/record/row-actions/default'

    // noinspection JSUnusedGlobalSymbols
    actionMoveUp(data) {
        const model = this.collection.get(data.id);

        if (!model) {
            return;
        }

        const index = this.collection.indexOf(model);

        if (index === 0) {
            return;
        }

        Espo.Ajax.postRequest('EmailFolder/action/moveUp', {id: model.id})
            .then(() => {
                this.collection.fetch();
            });
    }

    // noinspection JSUnusedGlobalSymbols
    actionMoveDown(data) {
        const model = this.collection.get(data.id);

        if (!model) {
            return;
        }

        const index = this.collection.indexOf(model);

        if ((index === this.collection.length - 1) && (this.collection.length === this.collection.total)) {
            return;
        }

        Espo.Ajax.postRequest('EmailFolder/action/moveDown', {id: model.id})
            .then(() => {
                this.collection.fetch();
            });
    }
}
