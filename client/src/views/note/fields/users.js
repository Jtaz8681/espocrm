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

import LinkMultipleFieldView from 'views/fields/link-multiple';

export default class extends LinkMultipleFieldView {

    init() {
        this.messagePermission = this.getAcl().getPermissionLevel('message');
        this.portalPermission = this.getAcl().getPermissionLevel('portal');

        if (this.messagePermission === 'no' && this.portalPermission === 'no') {
            this.readOnly = true;
        }

        super.init();
    }

    getSelectBoolFilterList() {
        if (this.messagePermission === 'team') {
            return ['onlyMyTeam'];
        }

        if (this.portalPermission === 'yes') {
            return null;
        }
    }

    getSelectPrimaryFilterName() {
        if (this.portalPermission === 'yes' && this.messagePermission === 'no') {
            return 'activePortal';
        }

        return 'active';
    }

    getSelectFilterList() {
        if (this.portalPermission === 'yes') {
            if (this.messagePermission === 'no') {
                 return ['activePortal'];
            }

            return ['active', 'activePortal'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    prepareEditItemElement(id, name) {
        const itemElement = super.prepareEditItemElement(id, name);

        const avatarHtml = this.getHelper().getAvatarHtml(id, 'small', 18, 'avatar-link');

        if (avatarHtml) {
            const img = new DOMParser().parseFromString(avatarHtml, 'text/html').body.childNodes[0];

            const textElement = itemElement.querySelector('.text');

            textElement?.prepend(img);
        }

        return itemElement;
    }
}
