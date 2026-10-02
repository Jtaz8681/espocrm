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

/** @module models/user */

import Model from 'model';

/**
 * A user.
 */
class User extends Model {

    name = 'User'
    entityType = 'User'
    urlRoot = 'User'

    /**
     * Is admin.
     *
     * @returns {boolean}
     */
    isAdmin() {
        return this.get('type') === 'admin' || this.isSuperAdmin();
    }

    /**
     * Is portal.
     *
     * @returns {boolean}
     */
    isPortal() {
        return this.get('type') === 'portal';
    }

    /**
     * Is API.
     *
     * @returns {boolean}
     */
    isApi() {
        return this.get('type') === 'api';
    }

    /**
     * Is regular.
     *
     * @returns {boolean}
     */
    isRegular() {
        return this.get('type') === 'regular';
    }

    /**
     * Is system.
     *
     * @returns {boolean}
     */
    isSystem() {
        return this.get('type') === 'system';
    }

    /**
     * Is super-admin.
     *
     * @returns {boolean}
     */
    isSuperAdmin() {
        return this.get('type') === 'super-admin';
    }
}

export default User;
