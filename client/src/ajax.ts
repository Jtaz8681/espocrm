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

/** @module ajax */

import $ from 'jquery';
import Utils from 'utils';
import {AjaxPromise, Xhr} from 'util/ajax';

let isConfigured: boolean = false;
let defaultTimeout: number;
let apiUrl: string;
let beforeSend: Handler;
let onSuccess: Handler;
let onError: Handler;
let onTimeout: Handler;
let onOffline: (() => void) | undefined;

type Handler = (xhr?: XMLHttpRequest, options?: Record<string, any>) => void;

/**
 * Options.
 */
interface Options {
    timeout?: number,
    headers?: Record<string, string>,
    dataType?: 'json' | 'text',
    contentType?: string,
    resolveWithXhr?: boolean,
}

const baseUrl = Utils.obtainBaseUrl();

// noinspection JSUnusedGlobalSymbols
/**
 * Functions for API HTTP requests.
 */
const Ajax = {

    /**
     * Request.
     *
     * @param url An URL.
     * @param method An HTTP method.
     * @param {*} [data] Data.
     * @param [options] Options.
     * @returns {AjaxPromise<any>}
     */
    request: function (
        url: string,
        method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH' | 'OPTIONS',
        data: any = undefined,
        options: Options & Record<string, any> = {}
    ): AjaxPromise {

        const timeout = 'timeout' in options ? options.timeout : defaultTimeout;
        const contentType = options.contentType || 'application/json';

        let body: any;

        if (options.data && !data) {
            data = options.data;
        }

        if (apiUrl) {
            url = Utils.trimSlash(apiUrl) + '/' + url;
        }

        if (!['GET', 'OPTIONS'].includes(method) && data) {
            body = data;

            if (contentType === 'application/json' && typeof data !== 'string') {
                body = JSON.stringify(data);
            }
        }

        if (method === 'GET' && data) {
            const part = $.param(data);

            url.includes('?') ?
                url += '&' :
                url += '?';

            url += part;
        }

        const urlObj = new URL(baseUrl + url);

        const xhr = new Xhr();

        if (timeout != null) {
            xhr.timeout = timeout;
        }

        xhr.open(method, urlObj);
        xhr.setRequestHeader('Content-Type', contentType);

        if (options.headers) {
            for (const key in options.headers) {
                xhr.setRequestHeader(key, options.headers[key]);
            }
        }

        if (beforeSend) {
            beforeSend(xhr, options);
        }

        const promiseWrapper: {
            promise?: AjaxPromise,
            xhr?: Xhr,
        } = {};

        const promise = new AjaxPromise<any>((resolve, reject) => {
            const onErrorGeneral = (isTimeout: boolean = false) => {
                if (options.error) {
                    options.error(xhr, options);
                }

                // @ts-ignore
                reject(xhr, options);

                if (isTimeout) {
                    if (onTimeout) {
                        onTimeout(xhr, options);
                    }

                    return;
                }

                if (xhr.status === 0 && !navigator.onLine && onOffline) {
                    onOffline();

                    return;
                }

                if (onError) {
                    onError(xhr, options);
                }
            };

            xhr.ontimeout = () => onErrorGeneral(true);
            xhr.onerror = () => onErrorGeneral();

            xhr.onload = () => {
                if (xhr.status >= 400) {
                    onErrorGeneral();

                    return;
                }

                let response: string | Xhr = xhr.responseText;

                if ((options.dataType || 'json') === 'json') {
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (e) {
                        console.error('Could not parse API response.');

                        onErrorGeneral();
                    }
                }

                if (options.success) {
                    options.success(response);
                }

                onSuccess(xhr, options);

                if (options.resolveWithXhr) {
                    response = xhr;
                }

                resolve(response)
            }

            xhr.send(body);

            if (promiseWrapper.promise) {
                promiseWrapper.promise.xhr = xhr;

                return;
            }

            promiseWrapper.xhr = xhr;
        });

        promiseWrapper.promise = promise;
        promise.xhr = promise.xhr ?? promiseWrapper.xhr ?? null;

        return promise;
    },

    /**
     * POST request.
     *
     * @param {string} url An URL.
     * @param [data] Data.
     * @param [options] Options.
     */
    postRequest: function (
        url: string,
        data?: any,
        options?: Options & Record<string, any>,
    ): Promise<any> & AjaxPromise {

        if (data) {
            data = JSON.stringify(data);
        }

        return Ajax.request(url, 'POST', data, options);
    },

    /**
     * PATCH request.
     *
     * @param url An URL.
     * @param [data] Data.
     * @param [options] Options.
     */
    patchRequest: function (
        url: string,
        data: any = undefined,
        options?: Options & Record<string, any>,
    ): Promise<any> & AjaxPromise {

        if (data) {
            data = JSON.stringify(data);
        }

        return Ajax.request(url, 'PATCH', data, options);
    },

    /**
     * PUT request.
     *
     * @param url An URL.
     * @param [data] Data.
     * @param [options] Options.
     */
    putRequest: function (
        url: string,
        data: any = undefined,
        options?: Options & Record<string, any>,
    ): Promise<any> & AjaxPromise {

        if (data) {
            data = JSON.stringify(data);
        }

        return Ajax.request(url, 'PUT', data, options);
    },

    /**
     * DELETE request.
     *
     * @param url An URL.
     * @param [data] Data.
     * @param [options] Options.
     */
    deleteRequest: function (
        url: string,
        data?: any,
        options?: Options & Record<string, any>,
    ): Promise<any> & AjaxPromise {

        if (data) {
            data = JSON.stringify(data);
        }

        return Ajax.request(url, 'DELETE', data, options);
    },

    /**
     * GET request.
     *
     * @param url An URL.
     * @param [data] Data.
     * @param [options] Options.
     */
    getRequest: function (
        url: string,
        data: any = undefined,
        options?: Options & Record<string, any>,
    ): Promise<any> & AjaxPromise {

        return Ajax.request(url, 'GET', data, options);
    },

    /**
     * @internal
     * @param options Options.
     */
    configure: function (
        options: {
            apiUrl: string,
            timeout: number,
            beforeSend: Handler,
            onSuccess: Handler,
            onError: Handler,
            onTimeout: Handler,
            onOffline?: () => void,
        }
    ) {

        if (isConfigured) {
            throw new Error("Ajax is already configured.");
        }

        apiUrl = options.apiUrl;
        defaultTimeout = options.timeout;
        beforeSend = options.beforeSend;
        onSuccess = options.onSuccess;
        onError = options.onError;
        onTimeout = options.onTimeout;
        onOffline = options.onOffline;

        isConfigured = true;
    },
};


// @ts-ignore
Espo.Ajax = Ajax;

export default Ajax;
