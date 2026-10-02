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

import DatetimeFieldView from 'views/fields/datetime';
import moment from 'moment';

class DateStartCallFieldView extends DatetimeFieldView {

    setup() {
        super.setup();

        this.notActualStatusList = [
            ...(this.getMetadata().get(`scopes.${this.entityType}.completedStatusList`) || []),
            ...(this.getMetadata().get(`scopes.${this.entityType}.canceledStatusList`) || []),
        ];
    }

    getAttributeList() {
        return [
            ...super.getAttributeList(),
            'dateEnd',
            'status',
        ];
    }

    data() {
        let style;

        const status = this.model.get('status');

        if (
            status &&
            !this.notActualStatusList.includes(status) &&
            (this.mode === this.MODE_DETAIL || this.mode === this.MODE_LIST)
        ) {
            if (this.isDateInPast('dateEnd')) {
                style = 'danger';
            } else if (this.isDateInPast('dateStart')) {
                style = 'warning';
            }
        }

        // noinspection JSValidateTypes
        return {
            ...super.data(),
            style: style,
        };
    }

    /**
     * @private
     * @param {string} field
     * @return {boolean}
     */
    isDateInPast(field) {
        const value = this.model.get(field);

        if (value) {
            const d = this.getDateTime().toMoment(value);
            const now = moment().tz(this.getDateTime().timeZone || 'UTC');

            if (d.unix() < now.unix()) {
                return true;
            }
        }

        return false;
    }
}

export default DateStartCallFieldView;
