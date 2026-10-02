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

import Controller from 'controller';

class LoginAsController extends Controller {

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {Record} options
     */
    actionLogin(options) {
        const anotherUser = options.anotherUser;
        const username = options.username;

        if (!anotherUser) {
            throw new Error("No anotherUser.");
        }

        this.baseController.login({
            anotherUser: anotherUser,
            username: username,
        });

        this.listenToOnce(this.baseController, 'login', () => {
            this.baseController.once('router-set', () => {
                const url = window.location.href.split('?')[0];

                window.location.replace(url);
            })
        });
    }
}

export default LoginAsController;
