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

import RowActionHandler from 'handlers/row-action';
import UserSelectPositionModalView from 'views/user/modals/select-position';

// noinspection JSUnusedGlobalSymbols
export default class ChangeUserTeamPositionRowActionHandler extends RowActionHandler {

    async process(model, action) {
        if (!model.collection || !model.collection.parentModel) {
            console.error(`Team model cannot be obtained.`);

            return;
        }

        const team = model.collection.parentModel;

        /** @type {string[]} */
        const positionList = team.attributes.positionList || [];
        const position = model.attributes.teamRole;

        const view = new UserSelectPositionModalView({
            position: position,
            positionList: positionList,
            name: model.attributes.name,
            onApply: position => {
                this.savePosition(team.id, model, position);
            },
        });

        await this.view.assignView('dialog', view);
        await view.render();
    }

    isAvailable(model, action) {
        if (!model.collection || !model.collection.parentModel) {
            return false;
        }

        if (!this.view.getAcl().checkModel(model, 'edit')) {
            return false;
        }

        if (!this.view.getUser().isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * @private
     * @param {string} teamId
     * @param {import('model').default} model
     * @param {string|null} position
     */
    async savePosition(teamId, model, position) {
        Espo.Ui.notifyWait();

        await Espo.Ajax.putRequest(`Team/${teamId}/userPosition`, {
            id: model.id,
            position: position,
        });

        model.setMultiple({teamRole: position});

        Espo.Ui.success(this.view.translate('Saved'));
    }
}
