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

/** @module view-record-helper */

import {Events} from 'bullbone';

export default class ViewRecordHelper {

    private state: {isChanged: boolean}

    private readonly fieldStateMap: Record<string, Record<string, any>>
    private readonly panelStateMap: Record<string, Record<string, any>>
    private readonly hiddenFields: Record<string, boolean>
    private readonly hiddenPanels: Record<string, boolean>
    private readonly fieldOptionListMap: Record<string, string[]>

    /**
     * @param [defaultFieldStates] Default field states.
     * @param [defaultPanelStates] Default panel states.
     */
    constructor(
        private defaultFieldStates: Record<string, any> = {},
        private defaultPanelStates: Record<string, any> = {},
    ) {
        this.fieldStateMap = {};
        this.panelStateMap = {};
        this.hiddenFields = {};
        this.hiddenPanels = {};
        this.fieldOptionListMap = {};

        this.state = {
            isChanged: false,
        };
    }

    /**
     * Get hidden fields.
     */
    getHiddenFields(): Record<string, boolean> {
        return this.hiddenFields;
    }

    /**
     * Get hidden panels.
     */
    getHiddenPanels(): Record<string, boolean> {
        return this.hiddenPanels;
    }

    /**
     * Set a field-state parameter.
     *
     * @param field A field name.
     * @param name A parameter.
     * @param value A value.
     */
    setFieldStateParam(field: string, name: string | 'hidden', value: unknown) {
        switch (name) {
            case 'hidden':
                if (value) {
                    this.hiddenFields[field] = true;
                } else {
                    delete this.hiddenFields[field];
                }

                break;
        }

        this.fieldStateMap[field] = this.fieldStateMap[field] || {};
        this.fieldStateMap[field][name] = value;

        this.trigger('field-change');
    }

    /**
     * Get a field-state parameter.
     *
     * @param field A field name.
     * @param name A parameter.
     * @returns A value.
     */
    getFieldStateParam(field: string, name: string): unknown {
        if (field in this.fieldStateMap) {
            if (name in this.fieldStateMap[field]) {
                return this.fieldStateMap[field][name];
            }
        }

        if (name in this.defaultFieldStates) {
            return this.defaultFieldStates[name];
        }

        return null;
    }

    /**
     * Set a panel-state parameter.
     *
     * @param {string} panel A panel name.
     * @param {string|'hidden'} name A parameter.
     * @param {*} value A value.
     */
    setPanelStateParam(panel: string, name: string | 'hidden', value: unknown) {
        switch (name) {
            case 'hidden':
                if (value) {
                    this.hiddenPanels[panel] = true;
                } else {
                    delete this.hiddenPanels[panel];
                }

                break;
        }

        this.panelStateMap[panel] = this.panelStateMap[panel] || {};
        this.panelStateMap[panel][name] = value;
    }

    /**
     * Get a panel-state parameter.
     *
     * @param panel A panel name.
     * @param name A parameter.
     * @returns A value.
     */
    getPanelStateParam(panel: string, name: string | 'hidden'): unknown {
        if (panel in this.panelStateMap) {
            if (name in this.panelStateMap[panel]) {
                return this.panelStateMap[panel][name];
            }
        }

        if (name in this.defaultPanelStates) {
            return this.defaultPanelStates[name];
        }

        return null;
    }

    /**
     * Set a field option list.
     *
     * @param field A field name.
     * @param list An option list.
     */
    setFieldOptionList(field: string, list: string[]) {
        this.fieldOptionListMap[field] = list;
    }

    /**
     * Clear a field option list.
     *
     * @param field A field name.
     */
    clearFieldOptionList(field: string) {
        delete this.fieldOptionListMap[field];
    }

    /**
     * Get a field option list.
     *
     * @param field A field name.
     * @returns Null if not set.
     */
    getFieldOptionList(field: string): string[] | null {
        return this.fieldOptionListMap[field] ?? null;
    }

    /**
     * Whether a field option list is set.
     *
     * @param field A field name.
     */
    hasFieldOptionList(field: string): boolean {
        return (field in this.fieldOptionListMap);
    }

    /**
     * Is changed.
     *
     * @since 9.2.0
     */
    isChanged(): boolean {
        return this.state.isChanged;
    }

    /**
     * Set is changed.
     *
     * @param isChanged
     * @since 9.2.0
     */
    setIsChanged(isChanged: boolean) {
        this.state.isChanged = isChanged;
    }

    /**
     * Subscribe to an event.
     *
     * @param {string} name An event.
     * @param {function(...any)} callback A callback.
     */
    on(name: string, callback: (...args: unknown[]) => any): this {
        Events.on.call(this, name, callback, arguments[2]);

        return this;
    }

    /**
     * Subscribe to an event. Fired once.
     *
     * @param {string} name An event.
     * @param {function(...any)} callback A callback.
     */
    once(name: string, callback: (...args: unknown[]) => void): this {
        Events.once.call(this, name, callback, arguments[2]);

        return this;
    }

    /**
     * Unsubscribe from an event or all events.
     *
     * @param {string} [name] From a specific event.
     * @param {function()} [callback] From a specific callback.
     */
    off(name?: string, callback?: (...args: unknown[]) => void): this {
        Events.off.call(this, name, callback, arguments[2]);

        return this;
    }

    /**
     * Subscribe to an event of other object.
     *
     * @param {Object} other What to listen.
     * @param {string} name An event.
     * @param callback A callback.
     */
    listenTo(other: object, name: string, callback: (...args: unknown[]) => void): this {
        Events.listenTo.call(this, other, name, callback);

        return this;
    }

    /**
     * Subscribe to an event of other object. Fired once. Will be automatically unsubscribed on view removal.
     *
     * @param {Object} other What to listen.
     * @param {string} name An event.
     * @param {function()} callback A callback.
     */
    listenToOnce(other: object, name: string, callback: (...args: unknown[]) => void): this {
        Events.listenToOnce.call(this, other, name, callback);

        return this;
    }

    /**
     * Stop listening to other object. No arguments will remove all listeners.
     *
     * @param {Object} [other] To remove listeners to a specific object.
     * @param {string} [name] To remove listeners to a specific event.
     * @param {function()} [callback] To remove listeners to a specific callback.
     */
    stopListening(other?: object, name?: string, callback?: (...args: unknown[]) => any): this {
        Events.stopListening.call(this, other, name, callback);

        return this;
    }

    /**
     * Trigger an event.
     *
     * @param {string} name An event.
     * @param {...*} parameters Arguments.
     */
    trigger(name: string, ...parameters: any[]): this {
        Events.trigger.call(this, name, ...parameters);

        return this;
    }
}
