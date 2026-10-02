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

import LinkFieldView from 'views/fields/link';

class UserDefaultTeamFieldView extends LinkFieldView {

    setup() {
        super.setup();

        this.validations.push('isUsers');
    }

    getOnEmptyAutocomplete() {
        const names = this.model.get('teamsNames') || {};

        const list = this.model.getTeamIdList().map(id => ({
            id: id,
            name: names[id] || id,
        }));

        return Promise.resolve(list);
    }

    // noinspection JSUnusedGlobalSymbols
    validateIsUsers() {
        const id = this.model.get('defaultTeamId');

        if (!id) {
            return false;
        }

        if (!this.model.has('teamsIds')) {
            // Mass update.
            return false;
        }

        if (this.model.getTeamIdList().includes(id)) {
            return false;
        }

        const msg = this.translate('defaultTeamIsNotUsers', 'messages', 'User')

        this.showValidationMessage(msg);

        return true;
    }
}

export default UserDefaultTeamFieldView;
