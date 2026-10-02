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

/** @module views/fields/url */

import VarcharFieldView, {VarcharOptions, VarcharParams} from 'views/fields/varchar';
import {BaseViewSchema, FieldValidator} from 'views/fields/base';


/**
 * Parameters.
 */
export interface UrlParams extends VarcharParams {
    /**
     * A max length.
     */
    maxLength?: number;
    /**
     * Required.
     */
    required?: boolean;
    /**
     * To display a copy-to-clipboard button.
     */
    copyToClipboard?: boolean;
    /**
     * To strip.
     */
    strip?: boolean;
    /**
     * Require protocol.
     * @since 10.0
     */
    protocolRequired?: boolean;
}

export interface UrlOptions extends VarcharOptions {}

/**
 * A URL field.
 *
 */
class UrlFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends UrlOptions = UrlOptions,
    P extends UrlParams = UrlParams,
> extends VarcharFieldView<S, O, P> {

    readonly type: string = 'url'

    protected listTemplate = 'fields/url/list'
    protected detailTemplate = 'fields/url/detail'
    protected defaultProtocol = 'https:'

    protected validations: (FieldValidator | string)[] = [
        'required',
        'valid',
        'maxLength',
    ]

    protected noSpellCheck: boolean = true
    protected optionalProtocol: boolean = true

    readonly DEFAULT_MAX_LENGTH = 255

    protected data(): Record<string, unknown> {
        const data = super.data();

        data.url = this.getUrl();

        return data;
    }

    protected setup() {
        super.setup();

        if (this.params.protocolRequired) {
            this.optionalProtocol = false;
        }
    }

    protected afterRender() {
        super.afterRender();

        if (this.isEditMode()) {
            this.$element?.on('change', () => {
                const value = (this.$element?.val() || '') as string;

                const parsedValue = this.parse(value);

                if (parsedValue === value) {
                    return;
                }

                const decoded = parsedValue ? this.decodeURI(parsedValue) : '';

                this.$element?.val(decoded);
            });
        }
    }

    protected getValueForDisplay(): string | null {
        const value = this.model.get(this.name);

        return value ? this.decodeURI(value) : null;
    }

    private decodeURI(value: string): string {
        try {
            return decodeURI(value);
        } catch (e) {
            console.warn(`Malformed URI ${value}.`);

            return value;
        }
    }

    protected parse(value: string): string {
        value = value.trim();

        if (this.params.strip && !this.params.protocolRequired) {
            value = this.strip(value);
        }

        try {
            if (decodeURIComponent(value) !== value) {
                return value;
            }

            value = encodeURI(value);
        } catch (e) {
            console.warn(`Malformed URI ${value}.`);

            return value;
        }

        return value;
    }

    private strip(value: string): string {
        if (value.indexOf('//') !== -1) {
            value = value.substring(value.indexOf('//') + 2);
        }

        value = value.replace(/\/+$/, '');

        return value;
    }

    protected getUrl(): string {
        let url = this.model.get(this.name);

        if (url && url !== '') {
            if (url.indexOf('//') === -1) {
                url = this.defaultProtocol + '//' + url;
            }

            return url;
        }

        return url;
    }

    validateValid() {
        const value = this.model.get(this.name);

        if (!value) {
            return false;
        }

        if (this.isValid(value)) {
            return false;
        }

        const msg = this.translate('fieldInvalid', 'messages')
            .replace('{field}', this.getLabelText());

        this.showValidationMessage(msg);

        return true;
    }

    private isValid(value: string): boolean {
        if (!this.optionalProtocol) {
            try {
                new URL(value);

                return true;
            } catch (e) {
                return false;
            }
        }

        const pattern = this.getMetadata().get(['app', 'regExpPatterns', 'uriOptionalProtocol', 'pattern']) as string;

        const regExp = new RegExp('^' + pattern + '$');

        if (regExp.test(value)) {
            return true;
        }

        return false;
    }

    // noinspection JSUnusedGlobalSymbols
    validateMaxLength() {
        const maxLength = this.params.maxLength || this.DEFAULT_MAX_LENGTH;

        const value = this.model.get(this.name);

        if (!value || !value.length) {
            return false;
        }

        if (value.length <= maxLength) {
            return false;
        }

        const msg = this.translate('fieldUrlExceedsMaxLength', 'messages')
            .replace('{maxLength}', maxLength.toString())
            .replace('{field}', this.getLabelText());

        this.showValidationMessage(msg);

        return true;
    }

    fetch(): Record<string, any> {
        const data = super.fetch();

        const value = data[this.name];

        if (!value) {
            return data;
        }

        data[this.name] = this.parse(value);

        return data;
    }
}

export default UrlFieldView;
