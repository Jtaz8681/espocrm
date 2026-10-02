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

class EmailAcl extends Acl {

    // noinspection JSUnusedGlobalSymbols
    checkModelRead(model, data, precise) {
        const result = this.checkModel(model, data, 'read', precise);

        if (result) {
            return true;
        }

        if (data === false) {
            return false;
        }

        const d = data || {};

        if (d.read === 'no') {
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

    checkIsOwner(model) {
        if (
            this.getUser().id === model.get('assignedUserId') ||
            this.getUser().id === model.get('createdById')
        ) {
            return true;
        }

        if (!model.has('assignedUsersIds')) {
            return null;
        }

        if (~(model.get('assignedUsersIds') || []).indexOf(this.getUser().id)) {
            return true;
        }

        return false;
    }

    // noinspection JSUnusedGlobalSymbols
    checkModelEdit(model, data, precise) {
        if (
            model.get('status') === 'Draft' &&
            model.get('createdById') === this.getUser().id
        ) {
            return true;
        }

        return this.checkModel(model, data, 'edit', precise);
    }

    checkModelDelete(model, data, precise) {
        const result = this.checkModel(model, data, 'delete', precise);

        if (result) {
            return true;
        }

        if (data === false) {
            return false;
        }

        const d = data || {};

        if (d.read === 'no') {
            return false;
        }

        if (model.get('createdById') === this.getUser().id) {
            if (model.get('status') !== 'Sent' && model.get('status') !== 'Archived') {
                return true;
            }
        }

        return result;
    }
}

export default EmailAcl;
