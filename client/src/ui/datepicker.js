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

import $ from 'jquery';
import Language from 'language';
import Settings from 'models/settings';
import {inject} from 'di';
import moment from 'moment';

/**
 * A datepicker.
 *
 * @since 9.0.0
 */
class Datepicker {


    /**
     * @private
     * @type {Language}
     */
    @inject(Language)
    language

    /**
     * @private
     * @type {Settings}
     */
    @inject(Settings)
    config

    /**
     * @param {HTMLElement} element
     * @param {{
     *     format: string,
     *     weekStart: number,
     *     todayButton?: boolean,
     *     date?: string,
     *     startDate?: string|undefined,
     *     onChange?: function(),
     *     hasDay?: function(string): boolean,
     *     hasMonth?: function(string): boolean,
     *     hasYear?: function(string): boolean,
     *     onChangeDate?: function(),
     *     onChangeMonth?: function(string),
     *     defaultViewDate?: string,
     * }} options
     */
    constructor(element, options) {
        /**
         * @private
         */
        this.$element = $(element);

        /**
         * @private
         * @type {string}
         */
        this.format = options.format;

        if (element instanceof HTMLInputElement) {
            if (options.date) {
                element.value = options.date;
            }

            let wait = false;

            this.$element.on('change', /** Record */e => {
                if (!wait) {
                    if (options.onChange) {
                        options.onChange();
                    }

                    wait = true;
                    setTimeout(() => wait = false, 100);
                }

                if (e.isTrigger && document.activeElement !== this.$element.get(0)) {
                    this.$element.focus();
                }
            });

            this.$element.on('click', () => this.show());
        } else {
            if (options.date) {
                element.dataset.date = options.date;
            }
        }

        const modalBodyElement = element.closest('.modal-body');

        const language = this.config.get('language');

        const format = options.format;

        const datepickerOptions = {
            autoclose: true,
            todayHighlight: true,
            keyboardNavigation: true,
            assumeNearbyYear: true,

            format: format.toLowerCase(),
            weekStart: options.weekStart,
            todayBtn: options.todayButton || false,
            startDate: options.startDate,
            orientation: 'bottom auto',
            templates: {
                leftArrow: '<span class="fas fa-chevron-left fa-sm"></span>',
                rightArrow: '<span class="fas fa-chevron-right fa-sm"></span>',
            },
            container: modalBodyElement ? $(modalBodyElement) : 'body',
            language: language,
            maxViewMode: 2,
            defaultViewDate: options.defaultViewDate,
        };

        if (options.hasDay) {
            datepickerOptions.beforeShowDay = (/** Date */date) => {
                const stringDate = moment(date).format(this.format);

                return {
                    enabled: options.hasDay(stringDate),
                };
            };
        }

        if (options.hasMonth) {
            datepickerOptions.beforeShowMonth = (/** Date */date) => {
                const stringDate = moment(date).format(this.format);

                return {
                    enabled: options.hasMonth(stringDate),
                };
            };
        }

        if (options.hasYear) {
            datepickerOptions.beforeShowYear = (/** Date */date) => {
                const stringDate = moment(date).format(this.format);

                return {
                    enabled: options.hasYear(stringDate),
                };
            };
        }

        // noinspection JSUnresolvedReference
        if (!(language in $.fn.datepicker.dates)) {
            // noinspection JSUnresolvedReference
            $.fn.datepicker.dates[language] = {
                days: this.language.get('Global', 'lists', 'dayNames'),
                daysShort: this.language.get('Global', 'lists', 'dayNamesShort'),
                daysMin: this.language.get('Global', 'lists', 'dayNamesMin'),
                months: this.language.get('Global', 'lists', 'monthNames'),
                monthsShort: this.language.get('Global', 'lists', 'monthNamesShort'),
                today: this.language.translate('Today'),
                clear: this.language.translate('Clear'),
            };
        }

        this.$element.datepicker(datepickerOptions)
            .on('changeDate', () => {
                if (options.onChangeDate) {
                    options.onChangeDate();
                }
            })
            .on('changeMonth', (/** {date: Date} */event) => {
                if (options.onChangeMonth) {
                    const dateString = moment(event.date).startOf('month').format(options.format);

                    options.onChangeMonth(dateString);
                }
            });

        if (element.classList.contains('input-group') && !(element instanceof HTMLInputElement)) {
            element.querySelectorAll('input').forEach(input => {
                $(input).on('click', () => $(input).datepicker('show'));
            });
        }
    }

    /**
     * Set a start date.
     *
     * @param {string|undefined} startDate
     */
    setStartDate(startDate) {
        this.$element.datepicker('setStartDate', startDate);
    }

    /**
     * Show.
     */
    show() {
        this.$element.datepicker('show');
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * Get the value.
     *
     * @return {string|null}
     */
    getDate() {
        const date = this.$element.datepicker('getDate');

        if (!date) {
            return null;
        }

        return moment(date).format(this.format);
    }

    /**
     * Refresh.
     */
    refresh() {
        const picker = this.$element.data('datepicker');

        if (!picker) {
            return;
        }

        picker.fill();
    }
}

export default Datepicker;
