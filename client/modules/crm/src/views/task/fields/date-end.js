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

import DatetimeOptionalFieldView from 'views/fields/datetime-optional';
import moment from 'moment';

class TaskDateEndFieldView extends DatetimeOptionalFieldView {

    isEnd = true

    getAttributeList() {
        return [
            ...super.getAttributeList(),
            'status',
        ];
    }

    data() {
        const data = super.data();

        const status = this.model.attributes.status;

        if (!status || this.notActualStatusList.includes(status)) {
            return data;
        }

        if (this.mode === this.MODE_DETAIL || this.mode === this.MODE_LIST) {
            if (this.isDateInPast()) {
                data.isOverdue = true;
            } else if (this.isDateToday()) {
                data.style = 'warning';
            }
        }

        if (data.isOverdue) {
            data.style = 'danger';
        }

        return data;
    }

    setup() {
        super.setup();

        this.notActualStatusList = [
            ...(this.getMetadata().get(`scopes.${this.entityType}.completedStatusList`) || []),
            ...(this.getMetadata().get(`scopes.${this.entityType}.canceledStatusList`) || []),
        ];

        if (this.isEditMode() || this.isDetailMode()) {
            this.on('change', () => {
                if (!this.model.get('dateEnd') && this.model.get('reminders')) {
                    this.model.set('reminders', []);
                }
            });
        }
    }

    /**
     * @private
     * @return {boolean}
     */
    isDateInPast() {
        if (this.isDate()) {
            const value = this.model.get(this.nameDate);

            if (value) {
                const d = moment.tz(value + ' 23:59', this.getDateTime().getTimeZone());
                const now = this.getDateTime().getNowMoment();

                if (d.unix() < now.unix()) {
                    return true;
                }
            }
        }

        const value = this.model.get(this.name);

        if (value) {
            const d = this.getDateTime().toMoment(value);
            const now = moment().tz(this.getDateTime().timeZone || 'UTC');

            if (d.unix() < now.unix()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @private
     * @return {boolean}
     */
    isDateToday() {
        if (!this.isDate()) {
            return false;
        }

        return this.getDateTime().getToday() === this.model.attributes[this.nameDate];
    }
}

export default TaskDateEndFieldView;
