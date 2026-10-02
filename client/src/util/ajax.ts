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

export class AjaxPromise<T = any> extends Promise<T> {

    /**
     * @type {XMLHttpRequest|null}
     * @internal
     */
    xhr: XMLHttpRequest | null = null

    isAborted = false

    /**
     * Abort the request.
     */
    abort() {
        this.isAborted = true;

        if (this.xhr) {
            this.xhr.abort();
        }
    }

    /**
     * Get a ready state.
     *
     */
    getReadyState(): number {
        if (!this.xhr) {
            return 0;
        }

        return this.xhr.readyState || 0;
    }

    /**
     * Get a status code
     *
     * @return {Number}
     */
    getStatus(): number {
        if (!this.xhr) {
            return 0;
        }

        return this.xhr.status;
    }
}

export class Xhr extends XMLHttpRequest {
    /**
     * To be set in an error handler to bypass default handling.
     */
    errorIsHandled = false
}
