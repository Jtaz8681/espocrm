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

import DateTime from 'date-time';
import {inject} from 'di';

const nowExpression = /return this\.dateTime\.getNow\(([0-9]+)\);/;
const shiftTodayExpression = /return this\.dateTime\.getDateShiftedFromToday\(([0-9]+), '([a-z]+)'\);/;
const shiftNowExpression = /return this\.dateTime\.getDateTimeShiftedFromNow\(([0-9]+), '([a-z]+)', ([0-9]+)\);/;

export default class DefaultValueProvider {

    /**
     * @type {DateTime}
     */
    @inject(DateTime)
    dateTime

    /**
     * Get a value.
     *
     * @param {string} key
     * @return {*}
     */
    get(key) {
        if (key === "return this.dateTime.getToday();") {
            return this.dateTime.getToday();
        }

        const matchNow = key.match(nowExpression);

        if (matchNow) {
            const multiplicity = parseInt(matchNow[1]);

            return this.dateTime.getNow(multiplicity);
        }

        const matchTodayShift = key.match(shiftTodayExpression);

        if (matchTodayShift) {
            const shift = parseInt(matchTodayShift[1]);
            const unit = matchTodayShift[2];

            return this.dateTime.getDateShiftedFromToday(shift, unit);
        }

        const matchNowShift = key.match(shiftNowExpression);

        if (matchNowShift) {
            const shift = parseInt(matchNowShift[1]);
            const unit = matchNowShift[2];
            const multiplicity = parseInt(matchNowShift[3]);

            return this.dateTime.getDateTimeShiftedFromNow(shift, unit, multiplicity);
        }

        return undefined;
    }
}
