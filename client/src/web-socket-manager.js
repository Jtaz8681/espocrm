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

/** @module web-socket-manager */

import Base64 from 'js-base64';

/**
 * A web-socket manager.
 */
class WebSocketManager {

    /**
     * @private
     * @type {number}
     */
    pingInterval = 60;

    /**
     * @private
     * @type {number}
     */
    reconnectInterval = 3;

    /**
     * @private
     */
    pingTimeout

    /**
     * @private
     * @type {boolean}
     */
    wasConnected = false

    /**
     * @private
     * @type {boolean}
     */
    isConnecting = false

    /**
     * @private
     * @type {number}
     */
    checkWakeInterval = 3

    /**
     * @private
     * @type {number}
     */
    checkWakeThresholdInterval = 5

    /**
     * @private
     * @type {boolean}
     */
    enabled = false

    /**
     * @param {import('models/settings').default} config A config.
     */
    constructor(config) {
        /**
         * @private
         * @type {import('models/settings').default}
         */
        this.config = config;

        /**
         * @private
         * @type {Function[]}
         */
        this.subscribeToReconnectQueue = [];

        /**
         * @private
         * @type {{category: string, callback: Function}[]}
         */
        this.subscribeQueue = [];

        /**
         * @private
         * @type {{category: string, callback: Function}[]}
         */
        this.subscriptions = [];

        /**
         * @private
         * @type {boolean}
         */
        this.isConnected = false;

        /**
         * @private
         */
        this.connection = null;

        /**
         * @private
         * @type {string}
         */
        this.url = '';

        /**
         * @private
         * @type {string}
         */
        this.protocolPart = '';

        const url = this.config.get('webSocketUrl');

        if (url) {
            if (url.indexOf('wss://') === 0) {
                this.url = url.substring(6);
                this.protocolPart = 'wss://';
            } else {
                this.url = url.substring(5);
                this.protocolPart = 'ws://';
            }
        } else {
            const siteUrl = this.config.get('siteUrl') || '';

            if (siteUrl.indexOf('https://') === 0) {
                this.url = siteUrl.substring(8);
                this.protocolPart = 'wss://';
            } else {
                this.url = siteUrl.substring(7);
                this.protocolPart = 'ws://';
            }

            if (~this.url.indexOf('/')) {
                this.url = this.url.replace(/\/$/, '');
            }

            const port = this.protocolPart === 'wss://' ? 443 : 8080;

            const si = this.url.indexOf('/');

            if (~si) {
                this.url = this.url.substring(0, si) + ':' + port;
            } else {
                this.url += ':' + port;
            }

            if (this.protocolPart === 'wss://') {
                this.url += '/wss';
            }
        }

        {
            let lastTime = Date.now();
            const interval = this.checkWakeInterval * 1000;
            const thresholdInterval = this.checkWakeThresholdInterval * 1000;

            setInterval(() => {
                const timeDiff = Date.now() - lastTime;
                lastTime = Date.now();

                if (timeDiff <= interval + thresholdInterval) {
                    return;
                }

                if (!this.isConnected || this.isConnecting) {
                    return;
                }

                if (this.pingTimeout) {
                    clearTimeout(this.pingTimeout);
                }

                this.connection.publish('', '');

                this.schedulePing()
            }, interval);
        }
    }

    /**
     * Connect.
     *
     * @param {string} auth An auth string.
     * @param {string} userId A user ID.
     */
    connect(auth, userId) {
        const authArray = Base64.decode(auth).split(':');
        const authToken = authArray[1];

        const url = `${this.protocolPart + this.url}?authToken=${authToken}&userId=${userId}`;

        try {
            this.connectInternal(auth, userId, url);
        } catch (e) {
            console.error(e.message);

            this.connection = null;
        }
    }

    /**
     * @private
     * @param {string} auth
     * @param {string} userId
     * @param {string} url
     */
    connectInternal(auth, userId, url) {
        this.isConnecting = true;

        this.connection = new ab.Session(
            url,
            () => {
                this.isConnecting = false;
                this.isConnected = true;

                this.subscribeQueue.forEach(item => {
                    this.subscribe(item.category, item.callback);
                });

                this.subscribeQueue = [];

                if (this.wasConnected) {
                    this.subscribeToReconnectQueue.forEach(callback => callback());
                }

                this.schedulePing();

                this.wasConnected = true;
            },
            code => {
                this.isConnecting = false;

                if (
                    code === ab.CONNECTION_LOST ||
                    code === ab.CONNECTION_UNREACHABLE
                ) {
                    if (this.isConnected) {
                        this.subscribeQueue = this.subscriptions;
                        this.subscriptions = [];
                    }

                    setTimeout(() => this.connect(auth, userId), this.reconnectInterval * 1000);
                } else if (code === ab.CONNECTION_CLOSED) {
                    this.subscribeQueue = [];
                }

                this.isConnected = false;
            },
            {skipSubprotocolCheck: true}
        );
    }

    /**
     * Subscribe to reconnecting.
     *
     * @param {function(): void} callback A callback.
     * @since 9.1.1
     */
    subscribeToReconnect(callback) {
        this.subscribeToReconnectQueue.push(callback);
    }

    /**
     * Unsubscribe from reconnecting.
     *
     * @param {function(): void} callback A callback.
     * @since 9.1.1
     */
    unsubscribeFromReconnect(callback) {
        this.subscribeToReconnectQueue = this.subscribeToReconnectQueue.filter(it => it !== callback);
    }

    /**
     * Subscribe to a topic.
     *
     * @param {string} category A topic.
     * @param {function(string, *): void} callback A callback.
     */
    subscribe(category, callback) {
        if (!this.connection) {
            return;
        }

        if (!this.isConnected) {
            this.subscribeQueue.push({
                category: category,
                callback: callback,
            });

            return;
        }

        try {
            this.connection.subscribe(category, callback);

            this.subscriptions.push({
                category: category,
                callback: callback,
            });
        } catch (e) {
            if (e.message) {
                console.error(e.message);
            } else {
                console.error("WebSocket: Could not subscribe to "+category+".");
            }
        }
    }

    /**
     * Unsubscribe.
     *
     * @param {string} category A topic.
     * @param {Function} [callback] A callback.
     */
    unsubscribe(category, callback) {
        if (!this.connection) {
            return;
        }

        this.subscribeQueue = this.subscribeQueue.filter(item => {
            if (callback === undefined) {
                return item.category !== category;
            }

            return item.category !== category || item.callback !== callback;
        });

        this.subscriptions = this.subscriptions.filter(item => {
            if (callback === undefined) {
                return item.category !== category;
            }

            return item.category !== category || item.callback !== callback;
        });

        try {
            this.connection.unsubscribe(category, callback);
        } catch (e) {
            if (e.message) {
                console.error(e.message);
            } else {
                console.error("WebSocket: Could not unsubscribe from "+category+".");
            }
        }
    }

    /**
     * Close a connection.
     */
    close() {
        this.stopPing();

        if (!this.connection) {
            return;
        }

        this.subscribeQueue = [];
        this.subscriptions = [];

        try {
            this.connection.close();
        } catch (e) {
            console.error(e.message);
        }

        this.isConnected = false;
        this.wasConnected = true;
    }

    /**
     * @private
     */
    stopPing() {
        this.pingTimeout = undefined;
    }

    /**
     * @private
     */
    schedulePing() {
        //ab._debugws = true;

        if (!this.connection) {
            this.stopPing();

            return;
        }

        this.pingTimeout = setTimeout(() => {
            if (!this.connection) {
                return;
            }

            if (!this.isConnecting) {
                this.connection.publish('', '');
            }

            this.schedulePing();
        }, this.pingInterval * 1000);
    }

    /**
     * @internal
     * @since 9.2.0
     */
    setEnabled() {
        this.enabled = true;
    }

    /**
     * Is enabled.
     *
     * @return {boolean}
     * @since 9.2.0
     */
    isEnabled() {
        return this.enabled;
    }
}

export default WebSocketManager;
