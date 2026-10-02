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

import ActionHandler from 'action-handler';

class CaseDetailActionHandler extends ActionHandler {

    close() {
        const model = this.view.model;

        model.save({status: 'Closed'}, {patch: true})
            .then(() => {
                Espo.Ui.success(this.view.translate('Closed', 'labels', 'Case'));
            });
    }

    reject() {
        const model = this.view.model;

        model.save({status: 'Rejected'}, {patch: true})
            .then(() => {
                Espo.Ui.success(this.view.translate('Rejected', 'labels', 'Case'));
            });
    }

    // noinspection JSUnusedGlobalSymbols
    isCloseAvailable() {
        return this.isStatusAvailable('Closed');
    }

    // noinspection JSUnusedGlobalSymbols
    isRejectAvailable() {
        return this.isStatusAvailable('Rejected');
    }

    isStatusAvailable(status) {
        const model = this.view.model;
        const acl = this.view.getAcl();
        const metadata = this.view.getMetadata();

        /** @type {string[]} */
        const notActualStatuses = metadata.get('entityDefs.Case.fields.status.notActualOptions') || [];

        if (notActualStatuses.includes(model.get('status'))) {
            return false;
        }

        if (!acl.check(model, 'edit')) {
            return false;
        }

        if (!acl.checkField(model.entityType, 'status', 'edit')) {
            return false;
        }

        const statusList = metadata.get(['entityDefs', 'Case', 'fields', 'status', 'options']) || [];

        if (!statusList.includes(status)) {
            return false;
        }

        return true;
    }
}

export default CaseDetailActionHandler;
