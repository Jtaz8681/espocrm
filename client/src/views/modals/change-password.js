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

import ModalView from 'views/modal';

class ChangePasswordModalView extends ModalView {

    template = 'modals/change-password'

    cssName = 'change-password'
    className = 'dialog dialog-record'

    setup() {
        this.buttonList = [
            {
                name: 'change',
                label: 'Change',
                style: 'danger',
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.headerText = this.translate('Change Password', 'labels', 'User');

        const promise = this.getModelFactory().create('User', user => {
            this.model = user;

            this.createView('currentPassword', 'views/fields/password', {
                model: user,
                mode: 'edit',
                selector:  '.field[data-name="currentPassword"]',
                defs: {
                    name: 'currentPassword',
                    params: {
                        required: true,
                    },
                },
            });

            this.createView('password', 'views/user/fields/password', {
                model: user,
                mode: 'edit',
                selector: '.field[data-name="password"]',
                defs: {
                    name: 'password',
                    params: {
                        required: true,
                    },
                },
            });

            this.createView('passwordConfirm', 'views/fields/password', {
                model: user,
                mode: 'edit',
                selector: '.field[data-name="passwordConfirm"]',
                defs: {
                    name: 'passwordConfirm',
                    params: {
                        required: true,
                    },
                },
            });
        });

        this.wait(promise);
    }

    /**
     * @param {string} field
     * @return {module:views/fields/base}
     */
    getFieldView(field) {
        return this.getView(field);
    }

    // noinspection JSUnusedGlobalSymbols
    actionChange() {
        this.getFieldView('currentPassword').fetchToModel();
        this.getFieldView('password').fetchToModel();
        this.getFieldView('passwordConfirm').fetchToModel();

        const notValid =
            this.getFieldView('currentPassword').validate() ||
            this.getFieldView('password').validate() ||
            this.getFieldView('passwordConfirm').validate();

        if (notValid) {
            return;
        }

        this.$el.find('button[data-name="change"]').addClass('disabled');

        Espo.Ajax
            .putRequest('UserSecurity/password', {
                currentPassword: this.model.get('currentPassword'),
                password: this.model.get('password'),
            })
            .then(() => {
                Espo.Ui.success(this.translate('passwordChanged', 'messages', 'User'));

                this.trigger('changed');
                this.close();
            })
            .catch(() => {
                this.$el.find('button[data-name="change"]').removeClass('disabled');
            });
    }
}

export default ChangePasswordModalView;
