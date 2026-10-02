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

import View from 'view';

class DetailModesView extends View {

    // language=Handlebars
    templateContent = `
        <div class="button-container clearfix">
            <div class="btn-group">
                {{#each modeDataList}}
                    <button
                        class="btn btn-text btn-xs-wide{{#if active}} active{{/if}}"
                        data-action="switchMode"
                        data-value="{{name}}"
                        {{#if ../disabled}}disabled="disabled"{{/if}}
                    >{{label}}</button>
                {{/each}}
            </div>
        </div>
    `

    /** @private */
    disabled = false

    /**
     * @param {{
     *     modeList: string[],
     *     mode: string,
     *     scope: string.
     * }} options
     */
    constructor(options) {
        super(options);

        /** @private */
        this.modeList = options.modeList;
        /** @private */
        this.mode = options.mode;
        /** @private */
        this.scope = options.scope;

        /**
         * @private
         * @type {Object.<string, boolean>}
         */
        this.hiddenMap = {};
    }

    data() {
        return {
            disabled: this.disabled,
            modeDataList: this.modeList
                .filter(mode => !this.hiddenMap[mode] || mode === this.mode)
                .map(mode => ({
                    name: mode,
                    active: mode === this.mode,
                    label: this.translate(mode, 'detailViewModes', this.scope),
                }))
        };
    }

    /**
     * Change mode.
     *
     * @param {string} mode
     * @return {Promise}
     */
    changeMode(mode) {
        this.mode = mode;

        return this.reRender();
    }

    /**
     * Hide a mode.
     *
     * @param {string} mode
     */
    async hideMode(mode) {
        this.hiddenMap[mode] = true;

        await this.reRender();
    }

    /**
     * Show a mode.
     *
     * @param {string} mode
     */
    async showMode(mode) {
        delete this.hiddenMap[mode];

        await this.reRender();
    }

    /**
     * Disable.
     *
     * @return {Promise}
     */
    disable() {
        this.disabled = true;

        return this.reRender();
    }

    /**
     * Enable.
     *
     * @return {Promise}
     */
    enable() {
        this.disabled = false

        return this.reRender();
    }
}

export default DetailModesView
