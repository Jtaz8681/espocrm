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

/** @module views/fields/date */

import BaseFieldView, {
    BaseOptions as BaseOptions,
    BaseParams as BaseParams,
    BaseViewSchema,
    FieldValidator,
} from 'views/fields/base';
import moment from 'moment';
import Datepicker from 'ui/datepicker';
import JQuery from 'jquery'

const $ = JQuery;

export interface DateParams extends BaseParams {
    /**
     * Required.
     */
    required?: boolean;
    /**
     * Use numeric format.
     */
    useNumericFormat?: boolean;
    /**
     * Validate to be after another date field.
     */
    after?: string;
    /**
     * Validate to be before another date field.
     */
    before?: string;
    /**
     * Allow an equal date for 'after' validation.
     */
    afterOrEqual?: boolean;
}

export interface DateOptions extends BaseOptions {
    /**
     * A label text of other field. Used in before/after validations.
     */
    otherFieldLabelText?: string;
}

/**
 * A date field.
 */
class DateFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends DateOptions = DateOptions,
    P extends DateParams = DateParams,
> extends BaseFieldView<S, O, P> {

    readonly type: string = 'date'

    protected listTemplate = 'fields/date/list'
    protected listLinkTemplate = 'fields/date/list-link'
    protected detailTemplate = 'fields/date/detail'
    protected editTemplate = 'fields/date/edit'
    protected searchTemplate = 'fields/date/search'

    protected validations: (FieldValidator | string)[] = [
        'required',
        'date',
        'after',
        'before',
    ]

    protected searchTypeList = [
        'lastSevenDays',
        'ever',
        'isEmpty',
        'currentMonth',
        'lastMonth',
        'nextMonth',
        'currentQuarter',
        'lastQuarter',
        'currentYear',
        'lastYear',
        'today',
        'past',
        'future',
        'lastXDays',
        'nextXDays',
        'olderThanXDays',
        'afterXDays',
        'on',
        'after',
        'before',
        'between',
    ]

    protected searchWithPrimaryTypeList: string[] = [
        'on',
        'notOn',
        'after',
        'before',
    ]

    protected searchWithRangeTypeList: string[] = [
        'between',
    ]

    protected searchWithAdditionalNumberTypeList: string[] = [
        'lastXDays',
        'nextXDays',
        'olderThanXDays',
        'afterXDays',
    ]

    /**
     * @inheritDoc
     */
    initialSearchIsNotIdle: boolean = true

    private datepicker: Datepicker | null = null

    protected useNumericFormat: boolean

    protected setup() {
        super.setup();

        if (this.getConfig().get('fiscalYearShift')) {
            this.searchTypeList = Espo.Utils.clone(this.searchTypeList);

            if (this.getConfig().get('fiscalYearShift') % 3 !== 0) {
                this.searchTypeList.push('currentFiscalQuarter');
                this.searchTypeList.push('lastFiscalQuarter');
            }

            this.searchTypeList.push('currentFiscalYear');
            this.searchTypeList.push('lastFiscalYear');
        }

        if (this.params.after) {
            this.listenTo(this.model, `change:${this.params.after}`, async () => {
                if (!this.isEditMode()) {
                    return;
                }

                await this.whenRendered();

                // Timeout prevents the picker popping one when the duration field adjusts the date end.
                setTimeout(() => {
                    this.onAfterChange();

                    this.datepicker?.setStartDate(this.getStartDateForDatePicker());
                }, 100);
            });
        }

        this.useNumericFormat = this.getConfig().get('readableDateFormatDisabled') || this.params.useNumericFormat;
    }

    protected data() {
        const data = super.data();

        data.dateValue = this.getDateStringValue();

        data.isNone = data.dateValue === null;

        if (data.dateValue === -1) {
            data.dateValue = null;
            data.isLoading = true;
        }

        if (this.isSearchMode()) {
            const value = this.getSearchParamsData().value || this.searchParams?.dateValue;
            const valueTo = this.getSearchParamsData().valueTo || this.searchParams?.dateValueTo;

            data.dateValue = this.getDateTime().toDisplayDate(value);
            data.dateValueTo = this.getDateTime().toDisplayDate(valueTo);

            if (this.searchWithAdditionalNumberTypeList.includes(this.getSearchType() ?? '')) {
                data.number = this.searchParams?.value;
            }
        }

        if (this.isListMode()) {
            data.titleDateValue = data.dateValue;
        }

        if (this.useNumericFormat) {
            data.useNumericFormat = true;
        }

        // noinspection JSValidateTypes
        return data;
    }

    setupSearch() {
        this.addHandler('change', 'select.search-type', (_e, target) => {
            this.handleSearchType((target as HTMLSelectElement).value);

            this.trigger('change');
        });

        this.addHandler('change', 'input.number', () => this.trigger('change'));
    }

    protected stringifyDateValue(value: any): string | null {
        if (!value) {
            if (
                this.mode === this.MODE_EDIT ||
                this.mode === this.MODE_SEARCH ||
                this.mode === this.MODE_LIST ||
                this.mode === this.MODE_LIST_LINK
            ) {
                return '';
            }

            return null;
        }

        if (
            this.mode === this.MODE_LIST ||
            this.mode === this.MODE_DETAIL ||
            this.mode === this.MODE_LIST_LINK
        ) {
            return this.convertDateValueForDetail(value);
        }

        return this.getDateTime().toDisplayDate(value);
    }

    protected convertDateValueForDetail(value: any): string | null {
        if (this.useNumericFormat) {
            return this.getDateTime().toDisplayDate(value);
        }

        const timezone = this.getDateTime().getTimeZone();
        const internalDateTimeFormat = this.getDateTime().internalDateTimeFormat;
        const readableFormat = this.getDateTime().getReadableDateFormat();
        const valueWithTime = value + ' 00:00:00';

        // @ts-ignore
        const today = moment.tz(timezone).startOf('day');
        // @ts-ignore
        let dateTime = moment.tz(valueWithTime, internalDateTimeFormat, timezone);

        const temp = today.clone();

        const ranges = {
            'today': [temp.unix(), temp.add(1, 'days').unix()],
            'tomorrow': [temp.unix(), temp.add(1, 'days').unix()],
            'yesterday': [temp.add(-3, 'days').unix(), temp.add(1, 'days').unix()],
        };

        if (dateTime.unix() >= ranges['today'][0] && dateTime.unix() < ranges['today'][1]) {
            return this.translate('Today');
        }

        if (dateTime.unix() >= ranges['tomorrow'][0] && dateTime.unix() < ranges['tomorrow'][1]) {
            return this.translate('Tomorrow');
        }

        if (dateTime.unix() >= ranges['yesterday'][0] && dateTime.unix() < ranges['yesterday'][1]) {
            return this.translate('Yesterday');
        }

        // Need to use UTC, otherwise there's a DST issue with old dates.
        dateTime = moment.utc(valueWithTime, internalDateTimeFormat);

        if (dateTime.format('YYYY') === today.format('YYYY')) {
            return dateTime.format(readableFormat);
        }

        return dateTime.format(readableFormat + ', YYYY');
    }

    protected getDateStringValue(): string | -1 | null {
        if (this.mode === this.MODE_DETAIL && !this.model.has(this.name)) {
            return -1;
        }

        const value = this.model.get(this.name);

        return this.stringifyDateValue(value);
    }

    protected getStartDateForDatePicker(): string | undefined {
        if (!this.isEditMode() || !this.params.after) {
            return undefined;
        }

        let date = this.model.attributes[this.params.after];

        if (date == null) {
            return undefined;
        }

        if (date.length > 10) {
            date = this.getDateTime().toDisplay(date);
            [date,] = date.split(' ');

            return date;
        }

        return this.getDateTime().toDisplayDate(date);
    }

    protected afterRender() {
        if (this.isEditMode() || this.isSearchMode()) {
            this.mainInputElement = this.element?.querySelector(`[data-name="${this.name}"]`);

            this.$element = $(this.mainInputElement as any);

            const options = {
                format: this.getDateTime().dateFormat,
                weekStart: this.getDateTime().weekStart,
                startDate: this.getStartDateForDatePicker(),
                todayButton: this.getConfig().get('datepickerTodayButton') || false,
            };

            this.datepicker = null;

            if (this.mainInputElement instanceof HTMLInputElement) {
                this.datepicker = new Datepicker(this.mainInputElement, {
                    ...options,
                    onChange: () => this.trigger('change'),
                });
            }

            if (this.isSearchMode()) {
                const additionalGroup = this.element?.querySelector('.input-group.additional') as HTMLElement | null;

                if (additionalGroup) {
                    new Datepicker(additionalGroup, options)

                    this.initDatePickerEventHandlers('input.filter-from');
                    this.initDatePickerEventHandlers('input.filter-to');
                }
            }

            const button = this.mainInputElement?.parentNode?.querySelector('button.date-picker-btn');

            if (button instanceof HTMLElement) {
                button.addEventListener('click', () => this.datepicker?.show());
            }

            if (this.isSearchMode()) {
                const type = this.fetchSearchType();

                this.handleSearchType(type);
            }
        }
    }

    /**
     * @private
     * @param {string} selector
     */
    initDatePickerEventHandlers(selector: string) {
        const input = this.element?.querySelector(selector);

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        $(input).on('change', (e: Record<string, any>) => {
            this.trigger('change');

            if (e.isTrigger) {
                if (document.activeElement !== input) {
                    input.focus({preventScroll: true});
                }
            }
        });
    }

    protected handleSearchType(type: string) {
        const primary = this.element?.querySelector('div.primary');
        const additional = this.element?.querySelector('div.additional');
        const additionalNumber = this.element?.querySelector('div.additional-number');

        primary?.classList.add('hidden');
        additional?.classList.add('hidden');
        additionalNumber?.classList.add('hidden');

        if (this.searchWithPrimaryTypeList.includes(type)) {
            primary?.classList.remove('hidden');

            return;
        }

        if (this.searchWithAdditionalNumberTypeList.includes(type)) {
            additionalNumber?.classList.remove('hidden');

            return;
        }

        if (this.searchWithRangeTypeList.includes(type)) {
            additional?.classList.remove('hidden');
        }
    }

    protected parseDate(string: string): string | -1 {
        return this.getDateTime().fromDisplayDate(string);
    }

    protected parse(string: string): string | -1 | null {
        if (!string) {
            return null;
        }

        return this.parseDate(string);
    }

    fetch(): Record<string, any> {
        const data = {} as Record<string, any>;

        data[this.name] = this.parse(this.mainInputElement?.value ?? '');

        return data;
    }

    fetchSearch(): Record<string, any> | null  {
        const type = this.fetchSearchType();

        if (this.searchWithRangeTypeList.includes(type)) {
            const inputFrom = this.element?.querySelector('input.filter-from');
            const inputTo = this.element?.querySelector('input.filter-to');

            const valueFrom = inputFrom instanceof HTMLInputElement ? this.parseDate(inputFrom.value) : undefined;
            const valueTo = inputTo instanceof HTMLInputElement ? this.parseDate(inputTo.value) : undefined;

            if (!valueFrom || !valueTo) {
                return null;
            }

            return {
                type: type,
                value: [valueFrom, valueTo],
                data: {
                    value: valueFrom,
                    valueTo: valueTo,
                },
            };
        }

        if (this.searchWithAdditionalNumberTypeList.includes(type)) {
            const input = this.element?.querySelector('input.number');

            const number = input instanceof HTMLInputElement ? input.value : undefined;

            return {
                type: type,
                value: number,
                date: true,
            };
        }

        if (this.searchWithPrimaryTypeList.includes(type)) {
            const input = this.element?.querySelector(`[data-name="${this.name}"]`);

            const value = input instanceof HTMLInputElement ? this.parseDate(input.value) : undefined;

            if (!value) {
                return null;
            }

            return {
                type: type,
                value: value,
                data: {
                    value: value,
                },
            };
        }

        if (type === 'isEmpty') {
            return {
                type: 'isNull',
                data: {
                    type: type,
                },
            };
        }

        return {
            type: type,
            date: true,
        };
    }

    protected getSearchType(): string | null {
        return this.getSearchParamsData().type ?? this.searchParams?.typeFront ?? this.searchParams?.type ?? null;
    }

    validateRequired() {
        if (!this.isRequired()) {
            return false;
        }

        if (this.model.get(this.name) === null) {
            const msg = this.translate('fieldIsRequired', 'messages')
                .replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }

        return false;
    }

    // noinspection JSUnusedGlobalSymbols
    validateDate() {
        if (this.model.get(this.name) === -1) {
            const msg = this.translate('fieldShouldBeDate', 'messages')
                .replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }
    }

    // noinspection JSUnusedGlobalSymbols
    validateAfter() {
        const field = this.params.after;

        if (!field) {
            return false;
        }

        const value = this.model.get(this.name);
        const otherValue = this.model.get(field);

        if (!(value && otherValue)) {
            return false;
        }

        const unix = moment(value).unix();
        const otherUnix = moment(otherValue).unix();

        if (this.params.afterOrEqual && unix === otherUnix) {
            return false;
        }

        if (unix <= otherUnix) {
            const otherFieldLabelText = this.options.otherFieldLabelText ||
                this.translate(field, 'fields', this.entityType);

            const msg = this.translate('fieldShouldAfter', 'messages')
                .replace('{field}', this.getLabelText())
                .replace('{otherField}', otherFieldLabelText);

            this.showValidationMessage(msg);

            return true;
        }

        return false;
    }

    // noinspection JSUnusedGlobalSymbols
    validateBefore() {
        const field = this.params.before;

        if (!field) {
            return false;
        }

        const value = this.model.get(this.name);
        const otherValue = this.model.get(field);

        if (!(value && otherValue)) {
            return;
        }

        if (moment(value).unix() >= moment(otherValue).unix()) {
            const msg = this.translate('fieldShouldBefore', 'messages')
                .replace('{field}', this.getLabelText())
                .replace('{otherField}', this.translate(field, 'fields', this.entityType));

            this.showValidationMessage(msg);

            return true;
        }
    }

    /**
     * @since 9.2.0
     */
    protected onAfterChange() {
        const after = this.params.after as string;

        const from = this.model.attributes[after] as string | undefined;
        const currentValue = this.model.attributes[this.name] as string | undefined;

        if (!from || !currentValue || from.length !== currentValue.length) {
            return;
        }

        if (
            this.getDateTime().toMomentDate(currentValue)
                .isBefore(this.getDateTime().toMomentDate(from))
        ) {
            this.model.set(this.name, from);
        }
    }
}

export default DateFieldView;
