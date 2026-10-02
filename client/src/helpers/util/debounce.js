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

/**
 * A debounce helper.
 *
 * @since 9.1.0
 */
export default class DebounceHelper {

    /**
     * @type {boolean}
     * @private
     */
    blocked = false

    /**
     * @type {boolean}
     * @private
     */
    blockedInProcess = false

    /**
     * @type {boolean}
     * @private
     */
    calledWhenProcessBlocked = false

    /**
     * @type {number}
     * @private
     */
    interval = 500

    /**
     * @type {number}
     * @private
     */
    blockInterval = 1000

    /**
     * @type {number}
     * @private
     */
    blockedCallCount = 0

    /**
     * @type {number|null}
     * @private
     */
    blockTimeoutId = null

    /**
     * @param {{
     *     handler: function(...*),
     *     interval?: number,
     *     blockInterval?: number,
     * }} options
     * @param options
     */
    constructor(options) {
        /**
         * @private
         * @type {function(...*)}
         */
        this.handler = options.handler;

        this.interval = options.interval ?? this.interval;
        this.blockInterval = options.blockInterval ?? this.blockInterval;
    }

    /**
     * Process.
     *
     * @param {...*} [arguments]
     */
    process() {
        const handle = () => {
            if (this.blocked) {
                this.blockedCallCount ++;

                return;
            }

            if (this.blockedInProcess) {
                this.calledWhenProcessBlocked = true;

                return;
            }

            this.handler(arguments);

            this.blockedInProcess = true;

            setTimeout(() => {
                const reRun = this.calledWhenProcessBlocked;

                this.blockedInProcess = false;
                this.calledWhenProcessBlocked = false;

                if (reRun) {
                    handle();
                }
            }, this.interval);
        };

        handle();
    }

    /**
     * Block for a while.
     *
     * @since 9.2.0
     */
    block() {
        this.blocked = true;

        if (this.blockTimeoutId) {
            clearTimeout(this.blockTimeoutId);
        }

        this.blockTimeoutId = setTimeout(() => {
            this.blocked = false;
            const toProcess = this.blockedCallCount > 1;
            this.blockedCallCount = 0;

            if (toProcess) {
                this.process();
            }
        }, this.blockInterval)
    }
}
