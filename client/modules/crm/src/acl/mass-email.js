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

class MassEmailAcl extends Acl {

    checkScope(data, action, precise, entityAccessData) {
        if (action === 'create') {
            return super.checkScope(data, 'edit', precise, entityAccessData);
        }

        return super.checkScope(data, action, precise, entityAccessData);
    }

    checkIsOwner(model) {
        if (model.has('campaignId')) {
            return true;
        }

        return super.checkIsOwner(model);
    }

    checkInTeam(model) {
        if (model.has('campaignId')) {
            return true;
        }

        return super.checkInTeam(model);
    }
}

export default MassEmailAcl;
