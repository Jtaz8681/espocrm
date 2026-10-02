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

import BaseFieldView from 'views/fields/base';

/**
 * @type {{
 *     edit: import('ace-builds').edit,
 *     require: import('ace-builds').require,
 * }}
 */
let ace;

export default class OAuthProviderAuthorizationParamsFieldView extends BaseFieldView {

    // language=Handlebars
    detailTemplateContent = `
        {{#if isNotEmpty}}
            <div id="{{containerId}}">{{value}}</div>
        {{else}}
            {{#if isSet}}
                <span class="none-value">{{translate 'None'}}</span>
            {{else}}
                <span class="loading-value"></span>
            {{/if}}
        {{/if}}
    `

    // language=Handlebars
    editTemplateContent = `
        <div id="{{containerId}}">{{value}}</div>
    `

    height = 46
    maxLineDetailCount = 80
    maxLineEditCount = 200

    data() {
        const data = super.data();

        const value = this.model.attributes[this.name];

        data.containerId = this.containerId;
        data.isNotEmpty = value != null;
        data.isSet = value !== undefined;

        try {
            data.value = value ? JSON.stringify(value, null, '  ') : null;
        } catch (e) {
            data.value = null;
        }

        return data;
    }

    setup() {
        super.setup();

        this.height = this.options.height || this.params.height || this.height;

        this.maxLineDetailCount =
            this.options.maxLineDetailCount ||
            this.params.maxLineDetailCount ||
            this.maxLineDetailCount;

        this.maxLineEditCount =
            this.options.maxLineEditCount ||
            this.params.maxLineEditCount ||
            this.maxLineEditCount;

        this.containerId = 'editor-' + Math.floor((Math.random() * 10000) + 1).toString();

        if (this.mode === this.MODE_EDIT || this.mode === this.MODE_DETAIL) {
            this.wait(
                this.requireAce()
            );
        }

        this.on('remove', () => {
            if (this.editor) {
                this.editor.destroy();
            }
        });

        this.validations.push(() => this.validateJson());
    }

    requireAce() {
        return Espo.loader.requirePromise('lib!ace')
            .then(lib => {
                ace = lib;

                const list = [
                    Espo.loader.requirePromise('lib!ace-ext-language_tools'),
                    Espo.loader.requirePromise('lib!ace-mode-json'),
                ];

                if (this.getThemeManager().getParam('isDark')) {
                    list.push(
                        Espo.loader.requirePromise('lib!ace-theme-tomorrow_night')
                    );
                }

                return Promise.all(list);
            });
    }

    afterRender() {
        super.afterRender();

        this.$editor = this.$el.find('#' + this.containerId);

        if (
            this.$editor.length &&
            (
                this.mode === this.MODE_EDIT ||
                this.mode === this.MODE_DETAIL ||
                this.mode === this.MODE_LIST
            )
        ) {
            this.$editor.css('fontSize', 'var(--font-size-base)');

            if (this.mode === this.MODE_EDIT) {
                this.$editor.css('minHeight', this.height + 'px');
            }

            const editor = this.editor = ace.edit(this.containerId);

            editor.setOptions({fontFamily: 'var(--font-family-monospace)'});
            editor.setFontSize('var(--font-size-base)');
            editor.container.style.lineHeight = 'var(--line-height-computed)';
            editor.renderer.updateFontSize();

            editor.setOptions({
                maxLines: this.mode === this.MODE_EDIT ? this.maxLineEditCount : this.maxLineDetailCount,
                enableLiveAutocompletion: true,
            });

            if (this.getThemeManager().getParam('isDark')) {
                editor.setOptions({
                    theme: 'ace/theme/tomorrow_night',
                });
            }

            if (this.isEditMode()) {
                editor.getSession().on('change', () => {
                    this.trigger('change', {ui: true});
                });

                editor.getSession().setUseWrapMode(true);
            }

            if (this.isReadMode()) {
                editor.setReadOnly(true);
                editor.renderer.$cursorLayer.element.style.display = 'none';
                editor.renderer.setShowGutter(false);
            }

            editor.setShowPrintMargin(false);
            editor.getSession().setUseWorker(false);
            editor.commands.removeCommand('find');
            editor.setHighlightActiveLine(false);

            const Mode = ace.require('ace/mode/json').Mode;

            editor.session.setMode(new Mode());
        }
    }

    /**
     * @private
     * @return {boolean}
     */
    validateJson() {
        const raw = this.editor.getValue();

        if (!raw) {
            return false;
        }

        try {
            JSON.parse(raw);
        } catch (e) {
            const message = this.translate('Not valid');

            this.showValidationMessage(message, '.ace_editor');

            return true;
        }

        return false;
    }

    fetch() {
        let value = null;

        const raw = this.editor.getValue();

        if (!raw) {
            return {[this.name]: null};
        }

        try {
            value = JSON.parse(raw);
        } catch (e) {}

        return {[this.name]: value};
    }
}
