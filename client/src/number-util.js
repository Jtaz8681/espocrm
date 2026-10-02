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

/** @module num-util */

/**
 * A number util.
 */
class NumberUtil {

    /**
     * @param {module:models/settings} config A config.
     * @param {module:models/preferences} preferences Preferences.
     */
    constructor(config, preferences) {
        /**
         * @private
         * @type {module:models/settings}
         */
        this.config = config;

        /**
         * @private
         * @type {module:models/preferences}
         */
        this.preferences = preferences;

        /**
         * A thousand separator.
         *
         * @private
         * @type {string|null}
         */
        this.thousandSeparator = null;

        /**
         * A decimal mark.
         *
         * @private
         * @type {string|null}
         */
        this.decimalMark = null;

        this.config.on('change', () => {
            this.thousandSeparator = null;
            this.decimalMark = null;
        });

        this.preferences.on('change', () => {
            this.thousandSeparator = null;
            this.decimalMark = null;
        });

        /**
         * A max decimal places.
         *
         * @private
         * @type {number}
         */
        this.maxDecimalPlaces = 10;
    }

    /**
     * Format an integer number.
     *
     * @param {number} value A value.
     * @returns {string}
     */
    formatInt(value) {
        if (value === null || value === undefined) {
            return '';
        }

        let stringValue = value.toString();

        stringValue = stringValue.replace(/\B(?=(\d{3})+(?!\d))/g, this.getThousandSeparator());

        return stringValue;
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * Format a float number.
     *
     * @param {number} value A value.
     * @param {number} [decimalPlaces] Decimal places.
     * @returns {string}
     */
    formatFloat(value, decimalPlaces) {
        if (value === null || value === undefined) {
            return '';
        }

        if (decimalPlaces === 0) {
            value = Math.round(value);
        }
        else if (decimalPlaces) {
            value = Math.round(value * Math.pow(10, decimalPlaces)) / (Math.pow(10, decimalPlaces));
        }
        else {
            value = Math.round(
                value * Math.pow(10, this.maxDecimalPlaces)) / (Math.pow(10, this.maxDecimalPlaces)
            );
        }

        const parts = value.toString().split('.');

        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.getThousandSeparator());

        if (decimalPlaces === 0) {
            return parts[0];
        }

        if (decimalPlaces) {
            let decimalPartLength = 0;

            if (parts.length > 1) {
                decimalPartLength = parts[1].length;
            }
            else {
                parts[1] = '';
            }

            if (decimalPlaces && decimalPartLength < decimalPlaces) {
                const limit = decimalPlaces - decimalPartLength;

                for (let i = 0; i < limit; i++) {
                    parts[1] += '0';
                }
            }
        }

        return parts.join(this.getDecimalMark());
    }

    /**
     * @private
     * @returns {string}
     */
    getThousandSeparator() {
        if (this.thousandSeparator !== null) {
            return this.thousandSeparator;
        }

        let thousandSeparator = '.';

        if (this.preferences.has('thousandSeparator')) {
            thousandSeparator = this.preferences.get('thousandSeparator');
        }
        else if (this.config.has('thousandSeparator')) {
            thousandSeparator = this.config.get('thousandSeparator');
        }

        /**
         * A thousand separator.
         *
         * @private
         * @type {string|null}
         */
        this.thousandSeparator = thousandSeparator;

        return thousandSeparator;
    }

    /**
     * @private
     * @returns {string}
     */
    getDecimalMark() {
        if (this.decimalMark !== null) {
            return this.decimalMark;
        }

        let decimalMark = '.';

        if (this.preferences.has('decimalMark')) {
            decimalMark = this.preferences.get('decimalMark');
        }
        else {
            if (this.config.has('decimalMark')) {
                decimalMark = this.config.get('decimalMark');
            }
        }

        /**
         * A decimal mark.
         *
         * @private
         * @type {string|null}
         */
        this.decimalMark = decimalMark;

        return decimalMark;
    }
}

export default NumberUtil;
