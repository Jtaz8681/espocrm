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

import {inject} from 'di';
import Settings from 'models/settings';
import User from 'models/user';

/**
 * An export helper.
 */
class ExportHelper {

    /**
     * @private
     * @type {Settings}
     */
    @inject(Settings)
    config

    /**
     * @private
     * @type {User}
     */
    @inject(User)
    user

    /**
     * @param {module:view} view A view.
     */
    constructor(view) {
        /**
         * @private
         * @type {module:view}
         */
        this.view = view;
    }

    /**
     * Check whether an export should be run in idle.
     *
     * @param {number} totalCount A total record count.
     * @returns {boolean}
     */
    checkIsIdle(totalCount) {
        if (this.user.isPortal()) {
            return false;
        }

        if (typeof totalCount === 'undefined') {
            totalCount = this.view.options.totalCount;
        }

        return totalCount === -1 || totalCount > this.config.get('exportIdleCountThreshold');
    }

    /**
     * Process export.
     *
     * @param {string} id An ID.
     * @returns {Promise<module:view>} Resolves with a dialog view.
     *   The view emits the 'close:success' event.
     */
    process(id) {
        Espo.Ui.notify(false);

        return new Promise(resolve => {
            this.view.createView('dialog', 'views/export/modals/idle', {id: id})
                .then(view => {
                    view.render();

                    resolve(view);

                    this.view.listenToOnce(view, 'success', data => {
                        resolve(data);

                        this.view.listenToOnce(view, 'close', () => {
                            view.trigger('close:success', data);
                        });
                    });
                });
        });
    }
}

export default ExportHelper;
