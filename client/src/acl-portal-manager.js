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

/** @module acl-portal-manager */

import AclManager from 'acl-manager';
import AclPortal from 'acl-portal';

/**
 * An access checking class for a specific scope for portals.
 */
class AclPortalManager extends AclManager {

    // noinspection JSUnusedGlobalSymbols
    /**
     * Check if a user in an account of a model.
     *
     * @param {module:model} model A model.
     * @returns {boolean|null} True if in an account, null if not clear.
     */
    checkInAccount(model) {
        const impl = /** @type {module:acl-portal} */this.getImplementation(model.entityType);

        return impl.checkInAccount(model);
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * Check if a user is a contact-owner to a model.
     *
     * @param {module:model} model A model.
     * @returns {boolean|null} True if in a contact-owner, null if not clear.
     */
    checkIsOwnContact(model) {
        const impl =
            /** @type {module:acl-portal} */
            this.getImplementation(model.entityType);

        return impl.checkIsOwnContact(model);
    }

    /**
     * @param {string} scope A scope.
     * @returns {module:acl-portal}
     */
    getImplementation(scope) {
        if (!(scope in this.implementationHash)) {
            let implementationClass = AclPortal;

            if (scope in this.implementationClassMap) {
                implementationClass = this.implementationClassMap[scope];
            }

            const params = {
                aclAllowDeleteCreated: false,
                forbiddenFieldList: this.getScopeForbiddenFieldList(scope),
            };

            this.implementationHash[scope] = new implementationClass(this.getUser(), scope, params, this);
        }

        return this.implementationHash[scope];
    }
}

export default AclPortalManager;
