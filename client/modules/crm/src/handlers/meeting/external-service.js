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

import {inject} from 'di';
import AppParams from 'app-params';

// noinspection JSUnusedGlobalSymbols
export default class MeetingExternalServiceHandler{

    /**
     * @private
     * @type {AppParams}
     */
    @inject(AppParams)
    appParams

    /**
     * @param {import('views/record/detail').default} view
     */
    constructor(view) {
        this.view = view;
    }

    process() {
        this.controlField();
        this.view.listenTo(this.view.model, 'change:externalService', () => this.controlField());
    }

    /**
     * @private
     */
    controlField() {
        const model = this.view.model;

        if (model.attributes.externalService) {
            this.view.showField('externalService');

            return;
        }

        const list = this.appParams.get('meetingServices') ?? [];

        if (!list.length) {
            this.view.hideField('externalService');
        }
    }
}
