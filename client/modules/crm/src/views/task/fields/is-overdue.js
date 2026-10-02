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

import BaseFieldView from 'views/fields/base';
import moment from 'moment';

// noinspection JSUnusedGlobalSymbols
export default class extends BaseFieldView {

    readOnly = true

    templateContent = `
        {{~#if isOverdue}}
        <span class="label label-danger">{{translate "overdue" scope="Task"}}</span>
        {{/if~}}
    `

    data() {
        let isOverdue = false;

        if (['Completed', 'Canceled'].indexOf(this.model.get('status')) === -1) {
            if (this.model.has('dateEnd')) {
                if (!this.isDate()) {
                    const value = this.model.get('dateEnd');

                    if (value) {
                        const d = this.getDateTime().toMoment(value);
                        const now = moment.tz(this.getDateTime().timeZone || 'UTC');

                        if (d.unix() < now.unix()) {
                            isOverdue = true;
                        }
                    }
                } else {
                    const value = this.model.get('dateEndDate');

                    if (value) {
                        const d = moment.utc(value + ' 23:59', this.getDateTime().internalDateTimeFormat);
                        const now = this.getDateTime().getNowMoment();

                        if (d.unix() < now.unix()) {
                            isOverdue = true;
                        }
                    }
                }
            }
        }

        return {
            isOverdue: isOverdue,
        };
    }

    setup() {
        this.mode = 'detail';
    }

    isDate() {
        const dateValue = this.model.get('dateEnd');

        if (dateValue) {
            return true;
        }

        return false;
    }
}
