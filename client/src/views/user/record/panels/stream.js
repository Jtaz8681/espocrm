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

import PanelStreamView from 'views/stream/panel';

export default class extends PanelStreamView {

    setup() {
        const model = /** @type import('models/user').default */this.model;

        if (this.model.id === this.getUser().id) {
            this.placeholderText = this.translate('writeMessageToSelf', 'messages');
        } else {
            this.placeholderText = this.translate('writeMessageToUser', 'messages')
                .replace('{user}', this.model.get('name'));
        }

        super.setup();

        this.setupPermission(model);
    }

    /**
     * @private
     * @param {import('models/user').default} model
     */
    setupPermission(model) {
        const permission = this.getAcl().checkPermission('message', model);

        if (permission) {
            return;
        }

        this.postDisabled = true;

        if (permission !== null) {
            return;
        }

        this.listenToOnce(this.model, 'sync', async () => {
            if (!this.getAcl().checkPermission('message', model)) {
                return;
            }

            this.postDisabled = false;

            await this.whenRendered();

            const container = this.element.querySelector('.post-container');

            if (container) {
                container.classList.remove('hidden');
            }
        });
    }

    prepareNoteForPost(model) {
        const userIdList = [this.model.id];
        const userNames = {};

        userNames[userIdList] = this.model.get('name');

        model.set('usersIds', userIdList);
        model.set('usersNames', userNames);
        model.set('targetType', 'users');
    }
}
