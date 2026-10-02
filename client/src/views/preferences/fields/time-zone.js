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

import EnumFieldView from 'views/fields/enum';

export default class extends EnumFieldView {

    setupOptions() {
        this.params.options = Espo.Utils.clone(this.getHelper().getAppParam('timeZoneList')) || [];

        this.translatedOptions = this.params.options.reduce((o, it) => {
            o[it] = it.replace('/', ' / ');

            return o;
        }, {});

        /** @type {string} */
        const systemValue = this.getConfig().get('timeZone') ?? '';
        const systemLabel = systemValue.replace('/', ' / ');

        this.params.options.unshift('');
        this.translatedOptions[''] = `${this.translate('Default')} · ${systemLabel}`;
    }
}
