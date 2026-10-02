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

import ActionHandler from 'action-handler';
import {inject} from 'di';
import Metadata from 'metadata';
import Ui from 'ui';

class DetailActions extends ActionHandler {

    /**
     * @type {string[]}
     * @private
     */
    historyStatusList

    /**
     * @private
     * @type {string|null}
     */
    completedStatusValue

    @inject(Metadata)
    metadata

    constructor(view) {
        super(view);

        /** @var string[]*/
        const completedStatusList = this.metadata.get(`scopes.Task.completedStatusList`, []);

        this.historyStatusList = [
            ...completedStatusList,
            ...this.metadata.get(`scopes.Task.canceledStatusList`, []),
        ];

        this.completedStatusValue = completedStatusList[0] ?? null;
    }

    async complete() {
        const model = this.view.model;

        Ui.notifyWait();

        await model.save({status: this.completedStatusValue}, {patch: true});

        // Needed for calendar update.
        this.view.trigger('after:save', model);

        Ui.success(this.view.getLanguage().translateOption('Completed', 'status', 'Task'));
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @return {boolean}
     */
    isCompleteAvailable() {
        const status = this.view.model.attributes.status;

        return !this.historyStatusList.includes(status);
    }
}

export default DetailActions;
