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

import RecordController from 'controllers/record';

class LayoutSetController extends RecordController {

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {Record} options
     */
    actionEditLayouts(options) {
        const id = options.id;

        if (!id) {
            throw new Error("ID not passed.");
        }

        this.main('views/layout-set/layouts', {
            layoutSetId: id,
            scope: options.scope,
            type: options.type,
        });
    }
}

export default LayoutSetController;
