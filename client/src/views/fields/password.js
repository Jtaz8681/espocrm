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

import BaseFieldView from 'views/fields/base';

class PasswordFieldView extends BaseFieldView {

    type = 'password'

    detailTemplate = 'fields/password/detail'
    editTemplate = 'fields/password/edit'

    validations = ['required', 'confirm']

    changePassword() {
        this.$el.find('[data-action="change"]').addClass('hidden');
        this.$element.removeClass('hidden');

        this.changing = true;
    }

    data() {
        return {
            isNew: this.model.isNew(),
            ...super.data(),
        }
    }

    setup() {
        super.setup();

        this.addActionHandler('change', () => this.changePassword());
    }

    // noinspection JSUnusedGlobalSymbols
    validateConfirm() {
        if (!this.model.has(this.name + 'Confirm')) {
            return;
        }

        if (this.model.get(this.name) !== this.model.get(this.name + 'Confirm')) {
            const msg = this.translate('fieldBadPasswordConfirm', 'messages')
                .replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }
    }

    afterRender() {
        super.afterRender();

        this.changing = false;

        if (this.params.readyToChange) {
            this.changePassword();
        }
    }

    fetch() {
        if (!this.model.isNew() && !this.changing) {
            return {};
        }

        return super.fetch();
    }
}

export default PasswordFieldView;
