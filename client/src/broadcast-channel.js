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

/** @module broadcast-channel */

class BroadcastChannel {

    constructor() {
        this.object = null;

        if (window.BroadcastChannel) {
            this.object = new window.BroadcastChannel('app');
        }
    }

    /**
     * Post a message.
     *
     * @param {string} message A message.
     */
    postMessage(message) {
        if (!this.object) {
            return;
        }

        this.object.postMessage(message);
    }

    /**
     * @callback module:broadcast-channel~callback
     *
     * @param {MessageEvent} event An event. A message can be obtained from the `data` property.
     */

    /**
     * Subscribe to a message.
     *
     * @param {module:broadcast-channel~callback} callback A callback.
     */
    subscribe(callback) {
        if (!this.object) {
            return;
        }

        this.object.addEventListener('message', callback);
    }
}

export default BroadcastChannel;
