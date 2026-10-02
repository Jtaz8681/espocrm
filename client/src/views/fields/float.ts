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

/** @module views/fields/float */

import IntFieldView from 'views/fields/int';
import {BaseOptions, BaseParams, BaseViewSchema, FieldValidator} from 'views/fields/base';

/**
 * Parameters.
 */
export interface FloatParams extends BaseParams {
    /**
     * A min value.
     */
    min?: number;
    /**
     * A max value.
     */
    max?: number;
    /**
     * Required.
     */
    required?: boolean;
    /**
     * Disable formatting.
     */
    disableFormatting?: boolean;
    /**
     * Decimal places.
     */
    decimalPlaces?: number | null;
}

/**
 * Options.
 */
export interface FloatOptions extends BaseOptions {}

/**
 * A float field.
 */
class FloatFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends FloatOptions = FloatOptions,
    P extends FloatParams = FloatParams,
> extends IntFieldView<S, O, P> {

    readonly type: string = 'float'

    protected editTemplate = 'fields/float/edit'

    decimalMark = '.'
    decimalPlacesRawValue = 10

    protected validations: (FieldValidator | string)[] = ['required', 'float', 'range']

    protected setup() {
        super.setup();

        if (this.getPreferences().has('decimalMark')) {
            this.decimalMark = this.getPreferences().get('decimalMark');
        }
        else if (this.getConfig().has('decimalMark')) {
            this.decimalMark = this.getConfig().get('decimalMark');
        }

        if (!this.decimalMark) {
            this.decimalMark = '.';
        }

        if (this.decimalMark === this.thousandSeparator) {
            this.thousandSeparator = '';
        }
    }

    protected setupAutoNumericOptions() {
        this.autoNumericOptions = {
            digitGroupSeparator: this.thousandSeparator || '',
            decimalCharacter: this.decimalMark,
            modifyValueOnWheel: false,
            selectOnFocus: false,
            decimalPlaces: this.decimalPlacesRawValue,
            decimalPlacesRawValue: this.decimalPlacesRawValue,
            allowDecimalPadding: false,
            showWarnings: false,
            // @ts-ignore
            formulaMode: true,
        };
    }

    protected getValueForDisplay(): string | null  {
        const value = isNaN(this.model.get(this.name)) ? null : this.model.get(this.name);

        return this.formatNumber(value);
    }

    protected formatNumberDetail(value: number | null): string {
        if (value === null) {
            return '';
        }

        const decimalPlaces = this.params.decimalPlaces;

        if (decimalPlaces === 0) {
            value = Math.round(value);
        } else if (decimalPlaces) {
            value = Math.round(value * Math.pow(10, decimalPlaces)) / (Math.pow(10, decimalPlaces));
        }

        const parts = value.toString().split(".");

        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandSeparator);

        if (decimalPlaces === 0) {
            return parts[0];
        }

        if (decimalPlaces) {
            let decimalPartLength = 0;

            if (parts.length > 1) {
                decimalPartLength = parts[1].length;
            } else {
                parts[1] = '';
            }

            if (decimalPlaces && decimalPartLength < decimalPlaces) {
                const limit = decimalPlaces - decimalPartLength;

                for (let i = 0; i < limit; i++) {
                    parts[1] += '0';
                }
            }
        }

        return parts.join(this.decimalMark);
    }

    validateFloat() {
        const value = this.model.get(this.name);

        if (isNaN(value)) {
            const msg = this.translate('fieldShouldBeFloat', 'messages')
                .replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }

        return false;
    }

    protected parse(input: string): number | string | null {
        let value = (input !== '') ? input : null;

        if (value === null) {
            return null;
        }

        value = value
            .split(this.thousandSeparator)
            .join('')
            .split(this.decimalMark)
            .join('.');

        return parseFloat(value);
    }

    fetch(): Record<string, unknown> {
        const valueString = (this.$element?.val() ?? '') as string;

        const value = this.parse(valueString);

        return {[this.name]: value};
    }
}

export default FloatFieldView;
