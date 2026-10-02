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

import ListExpandedRecordView from 'views/record/list-expanded';
import List from 'views/email/record/list';

export default class extends ListExpandedRecordView {

    // noinspection JSUnusedGlobalSymbols
    actionMarkAsImportant(data) {
        List.prototype.actionMarkAsImportant.call(this, data);
    }

    // noinspection JSUnusedGlobalSymbols
    actionMarkAsNotImportant(data) {
        List.prototype.actionMarkAsNotImportant.call(this, data);
    }

    // noinspection JSUnusedGlobalSymbols
    actionMoveToTrash(data) {
        List.prototype.actionMoveToTrash.call(this, data);
    }
}
