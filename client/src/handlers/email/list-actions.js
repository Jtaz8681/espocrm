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
import ImportEmlModal from 'views/email/modals/import-eml';

class EmailListActionsHandler extends ActionHandler {

    // noinspection JSUnusedGlobalSymbols
    async importEml() {
        const view = new ImportEmlModal();

        await this.view.assignView('dialog', view);
        await view.render();
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @return {boolean}
     */
    checkImportEml() {
        const acl = this.view.getAcl();

        return acl.checkScope('Email', 'create') &&
            acl.checkScope('Import');
    }
}

export default EmailListActionsHandler;
