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

import View from 'view';
import Ajax from 'ajax';

export default class extends View {

    template = 'admin/system-requirements/index'

    /** @type {Record} */
    requirements

    /**
     * @private
     * @type {boolean}
     */
    noAccess = false

    data() {
        return {
            phpRequirementList: this.requirements.php,
            databaseRequirementList: this.requirements.database,
            permissionRequirementList: this.requirements.permission,
            noAccess: this.noAccess,
        };
    }

    setup() {
        this.requirements = {};

        this.wait(this.loadData());
    }

    async loadData() {
        Espo.Ui.notifyWait();

        try {
            this.requirements = await Espo.Ajax.getRequest('Admin/action/systemRequirementList');
        } catch(e) {
            this.noAccess = true;

            if ('errorIsHandled' in e) {
                e.errorIsHandled = true;
            }
        }

        Espo.Ui.notify();
    }
}
