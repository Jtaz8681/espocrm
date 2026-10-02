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

import EnumIntFieldView from 'views/fields/enum-int';
import moment from 'moment';

class PreferencesCalendarScrollHourView extends EnumIntFieldView {

    setupOptions() {
        super.setupOptions();

        this.translatedOptions = {};
        this.translatedOptions[''] = this.translate('Default');

        const timeFormat = this.getDateTime().getTimeFormat();
        const today = this.getDateTime().getToday();

        this.params.options.forEach(item => {
            if (item === '') {
                return;
            }

            const itemString = today + ' ' + item.toString().padStart(2, '0') + ':00';

            this.translatedOptions[item] = moment.utc(itemString).format(timeFormat);
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default PreferencesCalendarScrollHourView;
