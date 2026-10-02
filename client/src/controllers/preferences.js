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
import Preferences from 'models/preferences';

class PreferencesController extends RecordController {

    defaultAction = 'own'

    getModel(callback, context) {
        const model = new Preferences({}, {
            defs: this.getMetadata().get('entityDefs.Preferences') || {},
        });

        model.setSettings(this.getConfig());

        if (callback) {
            callback.call(this, model);
        }

        return new Promise(resolve => {
            resolve(model);
        });
    }

    checkAccess(action) {
        return true;
    }

    // noinspection JSUnusedGlobalSymbols
    actionOwn() {
        this.actionEdit({id: this.getUser().id});
    }

    actionList(options) {}
}

export default PreferencesController;
