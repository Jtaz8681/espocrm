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

/** @module page-title */

import $ from 'jquery';

/**
 * A page-title util.
 */
class PageTitle {

    /**
     * @class
     * @param {module:models/settings} config A config.
     */
    constructor(config) {

        /**
         * @private
         * @type {boolean}
         */
        this.displayNotificationNumber = config.get('newNotificationCountInTitle') || false;

        /**
         * @private
         * @type {string}
         */
        this.title = $('head title').text() || '';

        /**
         * @private
         * @type {number}
         */
        this.notificationNumber = 0;
    }

    /**
     * Set a title.
     *
     * @param {string} title A title.
     */
    setTitle(title) {
        this.title = title;

        this.update();
    }

    /**
     * Set a notification number.
     *
     * @param {number} notificationNumber A number.
     */
    setNotificationNumber(notificationNumber) {
        this.notificationNumber = notificationNumber;

        if (this.displayNotificationNumber) {
            this.update();
        }
    }

    /**
     * Update a page title.
     */
    update() {
        let value = '';

        if (this.displayNotificationNumber && this.notificationNumber) {
            value = '(' + this.notificationNumber.toString() + ')';

            if (this.title) {
                value += ' ';
            }
        }

        value += this.title;

        $('head title').text(value);
    }
}

export default PageTitle;
