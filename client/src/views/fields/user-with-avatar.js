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

import UserFieldView from 'views/fields/user';

class UserWithAvatarFieldView extends UserFieldView {

    listTemplate = 'fields/user-with-avatar/list'
    detailTemplate = 'fields/user-with-avatar/detail'

    data() {
        const data = super.data();

        if (this.mode === this.MODE_DETAIL || this.mode === this.MODE_LIST) {
            data.avatar = this.getAvatarHtml();
            data.isOwn = this.model.get(this.idName) === this.getUser().id;
        }

        return data;
    }

    getAvatarHtml() {
        const size = this.mode === this.MODE_DETAIL ? 18: 16;

        return this.getHelper().getAvatarHtml(this.model.get(this.idName), 'small', size, 'avatar-link');
    }

    afterRender() {
        super.afterRender();

        if (this.isEditMode()) {
            this.controlEditModeAvatar();
        }
    }

    setup() {
        super.setup();

        this.addHandler('keydown', `input[data-name="${this.nameName}"]`, (/** KeyboardEvent */e, target) => {
            if (e.code === 'Enter') {
                return;
            }

            target.classList.add('being-typed');
        });

        this.addHandler('change', `input[data-name="${this.nameName}"]`, (e, target) => {
            setTimeout(() => target.classList.remove('being-typed'), 200);
        });

        this.addHandler('blur', `input[data-name="${this.nameName}"]`, (e, target) => {
            target.classList.remove('being-typed');
        });

        this.on('change', () => {
            if (!this.isEditMode()) {
                return;
            }

            const img = this.element.querySelector('img.avatar');

            if (img) {
                img.parentNode.removeChild(img);
            }

            this.controlEditModeAvatar();
        });
    }

    /**
     * @private
     */
    controlEditModeAvatar() {
        const nameElement = this.element.querySelector(`input[data-name="${this.nameName}"]`);
        nameElement.classList.remove('being-typed');

        const userId = this.model.attributes[this.idName];

        if (!userId) {
            return;
        }

        const avatarHtml = this.getHelper().getAvatarHtml(userId, 'small', 18, 'avatar-link');

        if (!avatarHtml) {
            return;
        }

        const img = new DOMParser().parseFromString(avatarHtml, 'text/html').body.childNodes[0];

        if (!(img instanceof HTMLImageElement)) {
            return;
        }

        img.classList.add('avatar-in-input')
        img.draggable = false;

        const input = this.element.querySelector('.input-group > input');

        if (!input) {
            return;
        }

        input.after(img);
    }
}

export default UserWithAvatarFieldView;
