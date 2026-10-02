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

/** @module handlers/login */

/**
 * Custom login handling. To be extended.
 *
 * @abstract
 */
class LoginHandler {

    /**
     * @param {module:views/login} loginView A login view.
     * @param {Object.<string, *>} data Additional metadata.
     */
    constructor(loginView, data) {
        /**
         * A login view.
         * @protected
         * @type {module:views/login}
         */
        this.loginView = loginView;

        /**
         * Additional metadata.
         * @protected
         * @type {Object.<string, *>}
         */
        this.data = data;
    }

    /**
     * Process. Called on 'Sign in' button click.
     *
     * @public
     * @abstract
     * @return {Promise<Object.<string, string>>} Resolved with headers to be sent to the `App/user` endpoint.
     */
    process() {
        return Promise.resolve({});
    }
}

export default LoginHandler;
