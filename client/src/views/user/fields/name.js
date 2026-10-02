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

import PersonNameFieldView from 'views/fields/person-name';

export default class extends PersonNameFieldView {

    listTemplate = 'user/fields/name/list-link'
    listLinkTemplate = 'user/fields/name/list-link'

    data() {
        const model = /** @type {import('models/user').default} */this.model;

        // noinspection JSValidateTypes
        return {
            ...super.data(),
            avatar: this.getAvatarHtml(),
            frontScope: model.isPortal() ? 'PortalUser': 'User',
            isOwn: this.model.id === this.getUser().id,
        };
    }

    getAvatarHtml() {
        return this.getHelper().getAvatarHtml(this.model.id, 'small', 20, 'avatar-link');
    }
}
