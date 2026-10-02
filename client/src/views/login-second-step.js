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
import Base64 from 'js-base64';
import $ from 'jquery';

class LoginSecondStepView extends View {

    /** @inheritDoc */
    template = 'login-second-step'

    /** @inheritDoc */
    views =  {
        footer: {
            fullSelector: 'body > footer',
            view: 'views/site/footer',
        },
    }

    /**
     * @type {string|null}
     * @private
     */
    anotherUser = null

    /**
     * Response from the first step.
     *
     * @type {Object.<string, *>}
     * @private
     */
    loginData =  null

    /**
     * Headers composed in the first step.
     *
     * @type {Object.<string, string>}
     * @private
     */
    headers =  null

    /** @private */
    isPopoverDestroyed =  false

    /** @inheritDoc */
    events = {
        /** @this LoginSecondStepView */
        'submit #login-form': function (e) {
            e.preventDefault();

            this.send();
        },
        /** @this LoginSecondStepView */
        'click [data-action="backToLogin"]': function () {
            this.trigger('back');
        },
        /** @this LoginSecondStepView */
        'keydown': function (e) {
            if (Espo.Utils.getKeyFromKeyEvent(e) === 'Control+Enter') {
                e.preventDefault();

                this.send();
            }
        },
    }

    /** @inheritDoc */
    data() {
        return {
            message: this.message,
        };
    }

    /** @inheritDoc */
    setup() {
        this.message = this.translate(this.options.loginData.message, 'messages', 'User');
        this.anotherUser = this.options.anotherUser || null;
        this.headers = this.options.headers || {};
        this.loginData = this.options.loginData;
    }

    /** @inheritDoc */
    afterRender() {
        this.$code = $('[data-name="field-code"]');
        this.$submit = this.$el.find('#btn-send');

        this.$code.focus();
    }

    /** @private */
    send() {
        const code = this.$code.val().trim().replace(/\s/g, '');

        const userName = this.options.userName;
        const token = this.loginData.token;
        const headers = Espo.Utils.clone(this.headers);

        if (code === '') {
            this.processEmptyCode();

            return;
        }

        this.disableForm();

        if (userName && token) {
            const authString = Base64.encode(userName + ':' + token);

            headers['Authorization'] = 'Basic ' + authString;
            headers['Espo-Authorization'] = authString;
        }

        headers['Espo-Authorization-Code'] = code;
        headers['Espo-Authorization-Create-Token-Secret'] = 'true';

        if (this.anotherUser !== null) {
            headers['X-Another-User'] = this.anotherUser;
        }

        this.notifyLoading();

        Espo.Ajax
            .getRequest('App/user', null, {
                login: true,
                headers: headers,
            })
            .then(data => {
                Espo.Ui.notify(false);

                this.triggerLogin(userName, data);
            })
            .catch(xhr => {
                this.undisableForm();


                if (xhr.status === 401) {
                    const statusReason = xhr.getResponseHeader('X-Status-Reason');

                    if (statusReason === 'error') {
                        this.onError();

                        return;
                    }

                    this.onWrongCredentials();
                }
            });
    }

    /**
     * Trigger login to proceed to the application.
     *
     * @private
     * @param {string} userName A username.
     * @param {Object.<string, *>} data Data returned from the `App/user` request.
     */
    triggerLogin(userName, data) {
        if (this.anotherUser) {
            data.anotherUser = this.anotherUser;
        }

        if (!userName) {
            userName = (data.user || {}).userName;
        }

        this.trigger('login', userName, data);
    }

    /** @private */
    processEmptyCode() {
        this.isPopoverDestroyed = false;

        const message = this.getLanguage().translate('codeIsRequired', 'messages', 'User');

        const $el = this.$code;

        $el
            .popover({
                placement: 'bottom',
                container: 'body',
                content: message,
                trigger: 'manual',
            })
            .popover('show');

        const $cell = $el.closest('.form-group');

        $cell.addClass('has-error');

        $el.one('mousedown click', () => {
            $cell.removeClass('has-error');

            if (this.isPopoverDestroyed) {
                return;
            }

            $el.popover('destroy');

            this.isPopoverDestroyed = true;
        });
    }

    /** @private */
    onFail(msg) {
        const $cell = $('#login .form-group');

        $cell.addClass('has-error');

        this.$el.one('mousedown click', () => {
            $cell.removeClass('has-error');
        });

        Espo.Ui.error(this.translate(msg, 'messages', 'User'));
    }

    /** @private */
    onError() {
        this.onFail('loginError');
    }

    /** @private */
    onWrongCredentials() {
        this.onFail('wrongCode');
    }

    /** @private */
    notifyLoading() {
        Espo.Ui.notifyWait();
    }

    /** @private */
    disableForm() {
        this.$submit.addClass('disabled').attr('disabled', 'disabled');
    }

    /** @private */
    undisableForm() {
        this.$submit.removeClass('disabled').removeAttr('disabled');
    }
}

// noinspection JSUnusedGlobalSymbols
export default LoginSecondStepView;
