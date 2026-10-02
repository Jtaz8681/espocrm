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

import View from 'view';
import Model from 'model';

export default class extends View {

    template = 'user/password-change-request'

    data() {
        return {
            requestId: this.options.requestId,
            notFound: this.options.notFound,
            notFoundMessage: this.notFoundMessage,
        };
    }

    setup() {
        this.addHandler('click', '#btn-submit', () => this.submit());

        const model = this.model = new Model();
        model.entityType = model.name = 'User';

        this.createView('password', 'views/user/fields/password', {
            model: model,
            mode: 'edit',
            selector: '.field[data-name="password"]',
            defs: {
                name: 'password',
                params: {
                    required: true,
                    maxLength: 255,
                },
            },
            strengthParams: this.options.strengthParams,
        });

        this.createView('passwordConfirm', 'views/fields/password', {
            model: model,
            mode: 'edit',
            selector: '.field[data-name="passwordConfirm"]',
            defs: {
                name: 'passwordConfirm',
                params: {
                    required: true,
                    maxLength: 255,
                },
            },
        });

        this.createView('generatePassword', 'views/user/fields/generate-password', {
            model: model,
            mode: 'detail',
            readOnly: true,
            selector: '.field[data-name="generatePassword"]',
            defs: {
                name: 'generatePassword',
            },
            strengthParams: this.options.strengthParams,
        });

        this.createView('passwordPreview', 'views/fields/base', {
            model: model,
            mode: 'detail',
            readOnly: true,
            selector: '.field[data-name="passwordPreview"]',
            defs: {
                name: 'passwordPreview',
            },
        });

        this.model.on('change:passwordPreview', () => this.reRender());

        const url = this.baseUrl = window.location.href.split('?')[0];

        this.notFoundMessage = this.translate('passwordChangeRequestNotFound', 'messages', 'User')
            .replace('{url}', url);
    }

    /**
     * @param {string} name
     * @return {import('views/fields/base').default}
     */
    getFieldView(name) {
        return /** @type {import('views/fields/base').default} */this.getView(name);
    }

    submit() {
        this.getFieldView('password').fetchToModel();
        this.getFieldView('passwordConfirm').fetchToModel();

        const notValid =
            this.getFieldView('password').validate() ||
            this.getFieldView('passwordConfirm').validate();

        const password = this.model.get('password');

        if (notValid) {
            return;
        }

        const $submit = this.$el.find('.btn-submit');

        $submit.addClass('disabled');

        Espo.Ajax
            .postRequest('User/changePasswordByRequest', {
                requestId: this.options.requestId,
                password: password,
            })
            .then(data => {
                this.$el.find('.password-change').remove();

                const url = data.url || this.baseUrl;

                const a = document.createElement('a');
                a.href = url;
                a.innerText = this.translate('Login', 'labels', 'User');

                const message = this.translate('passwordChangedByRequest', 'messages', 'User');

                const html = this.getHelper().escapeString(message) + ' ' + a.outerHTML;

                this.$el.find('.msg-box')
                    .removeClass('hidden')
                    .html('<span class="text-success">' + html + '</span>');
            })
            .catch(() => {
                return $submit.removeClass('disabled');
            });
    }
}
