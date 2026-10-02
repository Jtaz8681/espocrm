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

class ImportHandler extends ActionHandler {

    // noinspection JSUnusedGlobalSymbols
    errorExport() {
        Espo.Ajax
            .postRequest(`Import/${this.view.model.id}/exportErrors`)
            .then(data => {
                if (!data.attachmentId) {
                    const message = this.view.translate('noErrors', 'messages', 'Import');

                    Espo.Ui.warning(message);

                    return;
                }

                window.location = this.view.getBasePath() + '?entryPoint=download&id=' + data.attachmentId;
            });
    }
}

export default ImportHandler;
