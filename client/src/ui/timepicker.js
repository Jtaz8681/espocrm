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

import $ from 'jquery';

/**
 * A timepicker.
 */
class Timepicker {

    /**
     * @param {HTMLElement} element
     * @param {{
     *     step: number,
     *     timeFormat: string,
     *     scrollDefaultNow?: boolean,
     * }} options
     */
    constructor(element, options) {
        /**
         * @private
         */
        this.$element = $(element);

        const modalBodyElement = element.closest('.modal-body');

        this.$element.timepicker({
            step: options.step,
            timeFormat: options.timeFormat,
            appendTo: modalBodyElement ? $(modalBodyElement) : 'body',
            scrollDefaultNow: options.scrollDefaultNow || false,
        });
    }

    /**
     * Set the min time.
     *
     * @param {string|null} minTime
     */
    setMinTime(minTime) {
        this.$element.timepicker('option', 'minTime', minTime);
    }

    /**
     * Set the max time.
     *
     * @param {string|null} maxTime
     */
    setMaxTime(maxTime) {
        this.$element.timepicker('option', 'maxTime', maxTime);
    }

    /**
     * Add a 'change' event listener.
     *
     * @param {function} callback
     */
    addChangeEventListener(callback) {
        this.$element.on('change', callback);
    }

    /**
     * Show.
     */
    show() {
        this.$element.timepicker('show');
    }

    /**
     * Destroy.
     */
    destroy() {
        if (!this.$element[0]) {
            return;
        }

        this.$element.timepicker('remove');
    }
}

export default Timepicker;
