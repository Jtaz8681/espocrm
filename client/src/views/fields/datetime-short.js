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

/** @module views/fields/datetime-short */

import DatetimeFieldView from 'views/fields/datetime';
import moment from 'moment';

class DatetimeShortFieldView extends DatetimeFieldView {

    /**
     * @protected
     * @type {boolean}
     */
    shortInListMode = true

    /**
     * @protected
     * @type {boolean}
     */
    shortInDetailMode = true

    data() {
        const data = super.data();

        if (this.toApplyShort()) {
            data.titleDateValue = super.getDateStringValue();
        }

        return data;
    }

    /**
     * @private
     * @return {boolean}
     */
    toApplyShort() {
        return this.shortInListMode && this.mode === this.MODE_LIST ||
            this.shortInDetailMode && this.mode === this.MODE_DETAIL;
    }

    getDateStringValue() {
        if (!this.toApplyShort()) {
            return super.getDateStringValue();
        }

        const value = this.model.get(this.name);

        if (!value) {
            return super.getDateStringValue();
        }

        let timeFormat = this.getDateTime().timeFormat;

        if (this.params.hasSeconds) {
            timeFormat = timeFormat.replace(/:mm/, ':mm:ss');
        }

        const m = this.getDateTime().toMoment(value);
        const now = moment().tz(this.getDateTime().timeZone || 'UTC');
        const dt = now.clone().startOf('day');

        const ranges = {
            'today': [dt.unix(), dt.add(1, 'days').unix()],
            'tomorrow': [dt.unix(), dt.add(1, 'days').unix()],
            'yesterday': [dt.add(-3, 'days').unix(), dt.add(1, 'days').unix()]
        };

        if (
            m.unix() > ranges['yesterday'][0] &&
            m.unix() < ranges['yesterday'][1] &&
            this.getLanguage().has('yesterdayShort', 'strings', 'Global')
        ) {
            return this.translate('yesterdayShort', 'strings') + ' ' + m.format(timeFormat);
        }

        if (
            m.unix() > now.clone().startOf('day').unix() &&
            m.unix() < now.clone().add(1, 'days').startOf('day').unix()
        ) {
            return m.format(timeFormat);
        }

        const readableFormat = this.getDateTime().getReadableShortDateFormat();

        return m.format('YYYY') === now.format('YYYY') ?
            m.format(readableFormat) :
            m.format(readableFormat + ', YY');
    }
}

// noinspection JSUnusedGlobalSymbols
export default DatetimeShortFieldView;
