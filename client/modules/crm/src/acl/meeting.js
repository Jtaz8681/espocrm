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

import Acl from 'acl';

class MeetingAcl extends Acl {

    // noinspection JSUnusedGlobalSymbols
    checkModelRead(model, data, precise) {
        return this._checkModelCustom('read', model, data, precise);
    }

    // noinspection JSUnusedGlobalSymbols
    checkModelStream(model, data, precise) {
        return this._checkModelCustom('stream', model, data, precise);
    }

    _checkModelCustom(action, model, data, precise) {
        let result = this.checkModel(model, data, action, precise);

        if (result) {
            return true;
        }

        if (data === false) {
            return false;
        }

        let d = data || {};

        if (d[action] === 'no') {
            return false;
        }

        if (model.has('usersIds')) {
            if (~(model.get('usersIds') || []).indexOf(this.getUser().id)) {
                return true;
            }
        }
        else if (precise) {
            return null;
        }

        return result;
    }
}

export default MeetingAcl;

