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
 * @internal
 * Listen to model attribute change.
 *
 * @param {{
 *     owner: import('view').default | import('model').default | import('collection').default,
 *     target: import('model').default,
 *     attributes?: string[],
 *     once?: boolean,
 *     callback: function({
 *         ui: boolean|null,
 *         action: string|'ui'|'save'|'fetch'|'cancel-edit'|null,
 *         fromView: import('views/fields/base').default,
 *     }),
 * }} params
 * @return {{stop: function()}}
 * @since 10.0.0
 */
export function onModelChange(params) {
    const owner = params.owner;
    const target = params.target;
    const callback = params.callback;

    let event = 'change';

    if (params.attributes) {
        event = params.attributes
            .map(it => `change:${it}`)
            .join(' ');
    }

    /**
     * @param {Record} o
     */
    function callCallback(o) {
        callback({
            ui: o.ui ?? null,
            action: o.action ?? null,
            fromView: o.fromView ?? null,
        });
    }

    const wrappedCallback = params.attributes ?
        (m, v, o) => callCallback(o) :
        (m, o) => callCallback(o);

    function stop() {
        owner.stopListening(target, event, wrappedCallback);
    }

    const output = {stop};

    if (params.once) {
        owner.listenToOnce(target, event, wrappedCallback);

        return output;
    }

    owner.listenTo(target, event, wrappedCallback);

    return output;
}

/**
 * @internal
 * Listen to sync.
 *
 * @param {{
 *     owner: import('view').default | import('model').default | import('collection').default,
 *     target: import('model').default | import('collection').default,
 *     once?: boolean,
 *     callback: function({
 *         action: 'fetch'|'save'|'destroy'|null,
 *         response: *,
 *     }),
 * }} params
 * @return {{stop: function()}}
 * @since 10.0.0
 */
export function onSync(params) {
    const owner = params.owner;
    const target = params.target;
    const callback = params.callback;

    const event = 'sync';

    /**
     * @param {Record} o
     * @param {*} response
     */
    function wrappedCallback(o, response) {
        callback({
            action: o.action ?? null,
            response: response,
        });
    }

    function stop() {
        owner.stopListening(target, event, wrappedCallback);
    }

    const output = {stop};

    if (params.once) {
        owner.listenToOnce(target, event, wrappedCallback);

        return output;
    }

    owner.listenTo(target, event, wrappedCallback);

    return output;
}
