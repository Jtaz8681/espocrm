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
import {inject} from 'di';
import AppParams from 'app-params';

// noinspection JSUnusedGlobalSymbols
export default class ExternalServiceFieldView extends EnumFieldView {

    /**
     * @private
     * @type {AppParams}
     */
    @inject(AppParams)
    appParams

    setupOptions() {
        /** @type {{name: string}[]} */
        const list = this.appParams.get('meetingServices') ?? [];

        this.params.options = list.map(it => it.name);

        this.params.options.unshift('')
    }
}
