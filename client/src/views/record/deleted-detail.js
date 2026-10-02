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

import DetailRecordView from 'views/record/detail';

class DeletedDetailRecordView extends DetailRecordView {

    bottomView = null

    sideView = 'views/record/deleted-detail-side'

    setupBeforeFinal() {
        super.setupBeforeFinal();

        this.buttonList = [];
        this.dropdownItemList = [];

        this.addDropdownItem({
            name: 'restoreDeleted',
            label: 'Restore'
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionRestoreDeleted() {
        Espo.Ui.notifyWait();

        Espo.Ajax
            .postRequest(this.model.entityType + '/action/restoreDeleted', {id: this.model.id})
            .then(() => {
                Espo.Ui.notify(false);

                this.model.set('deleted', false);
                this.model.trigger('after:restore-deleted');
            });
    }
}

export default DeletedDetailRecordView;
