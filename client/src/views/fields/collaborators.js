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

export default class CollaboratorsFieldView extends LinkMultipleFieldView {

    init() {
        this.assignmentPermission = this.getAcl().getPermissionLevel('assignmentPermission');

        if (this.assignmentPermission === 'no') {
            this.readOnly = true;
        }

        super.init();
    }

    getSelectBoolFilterList() {
        if (this.assignmentPermission === 'team') {
            return ['onlyMyTeam'];
        }
    }

    getSelectPrimaryFilterName() {
        return 'active';
    }

    getDetailLinkHtml(id, name) {
        const html = super.getDetailLinkHtml(id);

        const avatarHtml = this.isDetailMode() ?
            this.getHelper().getAvatarHtml(id, 'small', 18, 'avatar-link') : '';

        if (!avatarHtml) {
            return html;
        }

        return `${avatarHtml}${html}`;
    }

    /** @inheritDoc */
    getOnEmptyAutocomplete() {
        if (this.params.autocompleteOnEmpty) {
            return undefined;
        }

        if (this.ids && this.ids.includes(this.getUser().id)) {
            return Promise.resolve([]);
        }

        return Promise.resolve([
            {
                id: this.getUser().id,
                name: this.getUser().attributes.name,
            }
        ]);
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
