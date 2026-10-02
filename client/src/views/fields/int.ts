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

/** @module views/fields/int */

import BaseFieldView, {BaseOptions, BaseParams, BaseViewSchema, FieldValidator} from 'views/fields/base';
import AutoNumeric from 'autonumeric';

/**
 * Parameters.
 */
export interface IntParams extends BaseParams {
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
}

/**
 * Options.
 */
export interface IntOptions extends BaseOptions {}

/**
 * An integer field.
 */
class IntFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends IntOptions = IntOptions,
    P extends Record<string, any> | IntParams = IntParams,
    ValueType extends number | string = number,
> extends BaseFieldView<S, O, P> {

    readonly type: string = 'int'

    protected listTemplate = 'fields/int/list'
    protected detailTemplate = 'fields/int/detail'
    protected editTemplate = 'fields/int/edit'
    protected searchTemplate = 'fields/int/search'

    protected validations: (FieldValidator | string)[] = [
        'required',
        'int',
        'range',
    ]

    protected thousandSeparator = ','

    protected searchTypeList = [
        'isNotEmpty',
        'isEmpty',
        'equals',
        'notEquals',
        'greaterThan',
        'lessThan',
        'greaterThanOrEquals',
        'lessThanOrEquals',
        'between',
    ]

    protected autoNumericOptions: import('autonumeric').Options

    protected autoNumericInstance: AutoNumeric | null = null

    protected disableFormatting: boolean = false

    protected setup() {
        if (this.getPreferences().has('thousandSeparator')) {
            this.thousandSeparator = this.getPreferences().get('thousandSeparator');
        } else if (this.getConfig().has('thousandSeparator')) {
            this.thousandSeparator = this.getConfig().get('thousandSeparator');
        }

        if (this.params.disableFormatting) {
            this.disableFormatting = true;
        }
    }

    protected setupFinal() {
        super.setupFinal();

        this.setupAutoNumericOptions();
    }

    protected setupAutoNumericOptions() {
        const separator = (!this.disableFormatting ? this.thousandSeparator : null) || '';
        let decimalCharacter = '.';

        if (separator === '.') {
            decimalCharacter = ',';
        }

        this.autoNumericOptions = {
            digitGroupSeparator: separator,
            decimalCharacter: decimalCharacter,
            modifyValueOnWheel: false,
            decimalPlaces: 0,
            selectOnFocus: false,
            // @ts-ignore
            formulaMode: true,
        };

        if (this.params.max != null && this.params.max > Math.pow(10, 6)) {
            this.autoNumericOptions.maximumValue = this.params.max.toString();
        }

        if (this.params.min != null && this.params.min < - Math.pow(10, 6)) {
            this.autoNumericOptions.minimumValue = this.params.min.toString();
        }
    }

    protected afterRender() {
        super.afterRender();

        if (this.mode === this.MODE_EDIT) {
            if (this.autoNumericOptions) {
                const element = this.$element?.get(0) as HTMLInputElement;

                this.autoNumericInstance = new AutoNumeric(element, null, this.autoNumericOptions);
            }
        }

        if (this.mode === this.MODE_SEARCH) {
            const $searchType = this.$el.find('select.search-type');

            this.handleSearchType($searchType.val());

            this.$el.find('select.search-type').on('change', () => {
                this.trigger('change');
            });

            this.$element?.on('input', () => {
                this.trigger('change');
            });

            const $inputAdditional = this.$el.find('input.additional');

            $inputAdditional.on('input', () => {
                this.trigger('change');
            });

            if (this.autoNumericOptions) {
                const element1 = this.$element?.get(0) as HTMLInputElement;
                const element2 = $inputAdditional.get(0) as HTMLInputElement;

                new AutoNumeric(element1, null, this.autoNumericOptions);
                new AutoNumeric(element2, null, this.autoNumericOptions);
            }
        }
    }

    protected data() {
        const data = super.data();

        if (this.model.get(this.name) !== null && typeof this.model.get(this.name) !== 'undefined') {
            data.isNotEmpty = true;
        }

        data.valueIsSet = this.model.has(this.name);

        if (this.isSearchMode()) {
            data.value = this.searchParams?.value;

            if (this.getSearchType() === 'between') {
                data.value = this.getSearchParamsData().value1 ?? this.searchParams?.value1;
                data.value2 = this.getSearchParamsData().value2 ?? this.searchParams?.value2;
            }
        }

        if (this.isEditMode()) {
            data.value = this.model.get(this.name);
        }

        // noinspection JSValidateTypes
        return data;
    }

    protected getValueForDisplay(): string | null {
        const value = isNaN(this.model.get(this.name)) ? null : this.model.get(this.name);

        return this.formatNumber(value);
    }

    protected formatNumber(value: ValueType | null): string | null {
        if (this.disableFormatting) {
            return value?.toString() ?? null;
        }

        return this.formatNumberDetail(value);
    }

    protected formatNumberDetail(value: ValueType | null): string {
        if (value === null) {
            return '';
        }

        let stringValue = value.toString();

        stringValue = stringValue.replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandSeparator);

        return stringValue;
    }

    protected setupSearch() {
        this.addHandler('change', 'select.search-type', (_e, target) => {
            this.handleSearchType((target as HTMLSelectElement).value);
        });
    }

    protected handleSearchType(type: string) {
        const $additionalInput = this.$el.find('input.additional');

        const $input = this.$el.find('input[data-name="' + this.name + '"]');

        if (type === 'between') {
            $additionalInput.removeClass('hidden');
            $input.removeClass('hidden');
        } else if (~['isEmpty', 'isNotEmpty'].indexOf(type)) {
            $additionalInput.addClass('hidden');
            $input.addClass('hidden');
        } else {
            $additionalInput.addClass('hidden');
            $input.removeClass('hidden');
        }
    }

    protected getMaxValue(): number | null {
        let maxValue = this.model.getFieldParam(this.name, 'max') ?? null;

        if (!maxValue && maxValue !== 0) {
            maxValue = null;
        }

        if ('max' in this.params) {
            maxValue = this.params.max;
        }

        return maxValue;
    }

    protected getMinValue(): number | null {
        if ('min' in this.params) {
            return this.params.min ?? null;
        }

        return this.model.getFieldParam(this.name, 'min') ?? null;
    }

    // noinspection JSUnusedGlobalSymbols
    validateInt(): boolean {
        const value = this.model.get(this.name);

        if (isNaN(value)) {
            const msg = this.translate('fieldShouldBeInt', 'messages').replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }

        return false;
    }

    // noinspection JSUnusedGlobalSymbols
    validateRange() {
        const value = this.model.get(this.name);

        if (value === null) {
            return false;
        }

        const minValue = this.getMinValue();
        const maxValue = this.getMaxValue();

        if (minValue !== null && maxValue !== null) {
            if (value < minValue || value > maxValue ) {
                const msg = this.translate('fieldShouldBeBetween', 'messages')
                    .replace('{field}', this.getLabelText())
                    .replace('{min}', minValue.toString())
                    .replace('{max}', maxValue.toString());

                this.showValidationMessage(msg);

                return true;
            }
        } else {
            if (minValue !== null) {
                if (value < minValue) {
                    const msg = this.translate('fieldShouldBeGreater', 'messages')
                        .replace('{field}', this.getLabelText())
                        .replace('{value}', minValue.toString());

                    this.showValidationMessage(msg);

                    return true;
                }
            } else if (maxValue !== null) {
                if (value > maxValue) {
                    const msg = this.translate('fieldShouldBeLess', 'messages')
                        .replace('{field}', this.getLabelText())
                        .replace('{value}', maxValue.toString());
                    this.showValidationMessage(msg);

                    return true;
                }
            }
        }

        return false;
    }

    validateRequired() {
        if (this.isRequired()) {
            const value = this.model.get(this.name);

            if (value === null || value === false) {
                const msg = this.translate('fieldIsRequired', 'messages')
                    .replace('{field}', this.getLabelText());

                this.showValidationMessage(msg);

                return true;
            }
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
            .join('');

        if (value.indexOf('.') !== -1 || value.indexOf(',') !== -1) {
            return NaN;
        }

        return parseInt(value);
    }

    fetch(): Record<string, unknown> {
        const valueString = (this.$element?.val() ?? '') as string;

        const value = this.parse(valueString);

        const data = {} as any;

        data[this.name] = value;

        return data;
    }

    fetchSearch(): Record<string, any> | null {
        const value = this.parse((this.$element?.val() ?? '') as string);

        const type = this.fetchSearchType();

        let data: any;

        if (typeof value === 'number' && isNaN(value)) {
            return null;
        }

        if (type === 'between') {
            const valueTo = this.parse(this.$el.find('input.additional').val());

            if (typeof valueTo === 'number' && isNaN(valueTo)) {
                return null;
            }

            data = {
                type: type,
                value: [value, valueTo],
                data: {
                    value1: value,
                    value2: valueTo
                }
            };
        } else if (type === 'isEmpty') {
            data = {
                type: 'isNull',
                typeFront: 'isEmpty'
            };
        } else if (type === 'isNotEmpty') {
            data = {
                type: 'isNotNull',
                typeFront: 'isNotEmpty'
            };
        } else {
            data = {
                type: type,
                value: value,
                data: {
                    value1: value
                }
            };
        }

        return data;
    }

    protected getSearchType(): string | null {
        return this.searchParams?.typeFront ?? this.searchParams?.type ?? null;
    }
}

export default IntFieldView;
