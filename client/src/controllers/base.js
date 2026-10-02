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

/** @module controllers/base */

import Controller from 'controller';
import BaseView from 'views/base';

/**
 * A base controller.
 */
class BaseController extends Controller {

    constructor(params, injections) {
        super(params, injections);

        this.on('logout', () => this._clearAllStoredMainViews());
    }

    /**
     * @private
     */
    _clearAllStoredMainViews() {
        for (const name in this.params) {
            if (!name.startsWith('mainView-')) {
                continue;
            }

            const [, scope, key] = name.split('-', 3);
            const actualKey = `mainView-${scope}-${key}`;

            const view =
                /** @type {import('view').default} */
                this.get(actualKey);

            if (view) {
                view.remove(true);
            }

            this.unset(actualKey);
        }
    }

    /**
     * Clear a stored main view.
     *
     * @param {string} scope
     */
    clearScopeStoredMainView(scope) {
        for (const key in this.params) {
            if (!key.startsWith(`mainView-${scope}-`)) {
                continue;
            }

            const view =
                /** @type {import('view').default} */
                this.get(key);

            if (view) {
                view.remove(true);
            }

            this.unset(key);
        }
    }

    /**
     * Log in.
     *
     * @param {{
     *     anotherUser?: string,
     *     username?: string,
     * }} [options]
     */
    login(options) {
        const viewName = this.getConfig().get('loginView') || 'views/login';

        const anotherUser = (options || {}).anotherUser;
        const prefilledUsername = (options || {}).username;

        const viewOptions = {
            anotherUser: anotherUser,
            prefilledUsername: prefilledUsername,
        };

        this.entire(viewName, viewOptions, loginView => {
            loginView.render();

            loginView.on('login', (userName, data) => {
                this.trigger('login', this.normalizeLoginData(userName, data));
            });

            loginView.once('redirect', (viewName, headers, userName, password, data) => {
                loginView.remove();

                this.entire(viewName, {
                    loginData: data,
                    userName: userName,
                    password: password,
                    anotherUser: anotherUser,
                    headers: headers,
                }, secondStepView => {
                    secondStepView.render();

                    secondStepView.once('login', (userName, data) => {
                        this.trigger('login', this.normalizeLoginData(userName, data));
                    });

                    secondStepView.once('back', () => {
                        secondStepView.remove();

                        this.login();
                    });
                });
            });
        });
    }

    /** @private */
    normalizeLoginData(userName, data) {
        return {
            auth: {
                userName: userName,
                token: data.token,
                anotherUser: data.anotherUser,
            },
            user: data.user,
            preferences: data.preferences,
            acl: data.acl,
            settings: data.settings,
            appParams: data.appParams,
            language: data.language,
        };
    }

    /**
     * Log out.
     */
    logout() {
        const title = this.getConfig().get('applicationName') || 'EspoCRM';

        $('head title').text(title);

        this.trigger('logout');
    }

    /**
     * Clear cache.
     */
    clearCache() {
        this.entire('views/clear-cache', {
            cache: this.getCache(),
        }, view => {
            view.render();
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionLogin() {
        this.login();
    }

    // noinspection JSUnusedGlobalSymbols
    actionLogout() {
        this.logout();
    }

    // noinspection JSUnusedGlobalSymbols
    actionLogoutWait() {
        this.entire('views/base', {template: 'logout-wait'}, view => {
            view.render()
                .then(() => Espo.Ui.notifyWait())
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionClearCache() {
        this.clearCache();
    }

    /**
     * Error Not Found.
     */
    error404() {
        const view = new BaseView({template: 'errors/404'});

        this.entire(view);
    }

    /**
     * Error Forbidden.
     */
    error403() {
        const view = new BaseView({template: 'errors/403'});

        this.entire(view);
    }

    // noinspection JSUnusedGlobalSymbols
    actionError404() {
        this.error404();
    }

    // noinspection JSUnusedGlobalSymbols
    actionError403() {
        this.error403();
    }
}

export default BaseController;
