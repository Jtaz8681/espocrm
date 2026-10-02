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

class DateStartMeetingFieldView extends DatetimeOptionalFieldView {

    emptyTimeInInlineEditDisabled = true

    setup() {
        super.setup();

        this.noneOption = this.translate('All-Day', 'labels', 'Meeting');

        this.notActualStatusList = [
            ...(this.getMetadata().get(`scopes.${this.entityType}.completedStatusList`) || []),
            ...(this.getMetadata().get(`scopes.${this.entityType}.canceledStatusList`) || []),
        ];
    }

    getAttributeList() {
        return [
            ...super.getAttributeList(),
            'dateEnd',
            'dateEndDate',
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
            } else if (this.isDateInPast('dateStart', true)) {
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
     * @param {boolean} [isFrom]
     * @return {boolean}
     */
    isDateInPast(field, isFrom) {
        if (this.isDate()) {
            const value = this.model.get(field + 'Date');

            if (value) {
                const timeValue = isFrom ? value + ' 00:00' : value + ' 23:59';

                const d = moment.tz(timeValue, this.getDateTime().getTimeZone());
                const now = this.getDateTime().getNowMoment();

                if (d.unix() < now.unix()) {
                    return true;
                }
            }

            return false;
        }

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

    afterRender() {
        super.afterRender();

        if (this.isEditMode()) {
            this.controlTimePartVisibility();
        }
    }

    fetch() {
        const data = super.fetch();

        if (data[this.nameDate]) {
            data.isAllDay = true;
        } else {
            data.isAllDay = false;
        }

        return data;
    }

    controlTimePartVisibility() {
        if (!this.isEditMode()) {
            return;
        }

        if (!this.isInlineEditMode()) {
            return;
        }

        if (this.model.get('isAllDay')) {
            this.$time.addClass('hidden');
            this.$el.find('.time-picker-btn').addClass('hidden');
        } else {
            this.$time.removeClass('hidden');
            this.$el.find('.time-picker-btn').removeClass('hidden');
        }
    }
}

export default DateStartMeetingFieldView;
