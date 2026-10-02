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

import VarcharFieldView from 'views/fields/varchar';

export default class extends VarcharFieldView {

    setup() {
        super.setup();

        this.validations.push(() => this.validateUserName());
    }

    afterRender() {
        super.afterRender();

        const userNameRegularExpression = this.getUserNameRegularExpression();

        if (this.isEditMode()) {
            this.$element.on('change', () => {
                let value = this.$element.val();
                const re = new RegExp(userNameRegularExpression, 'gi');

                value = value
                    .replace(re, '')
                    .replace(/[\s]/g, '_')
                    .toLowerCase();

                this.$element.val(value);
                this.trigger('change');
            });
        }
    }

    getUserNameRegularExpression() {
        return this.getConfig().get('userNameRegularExpression') || '[^a-z0-9\-@_\.\s]';
    }

    validateUserName() {
        const value = this.model.get(this.name);

        if (!value) {
            return;
        }

        const userNameRegularExpression = this.getUserNameRegularExpression();

        const re = new RegExp(userNameRegularExpression, 'gi');

        if (!re.test(value)) {
            return;
        }

        const msg = this.translate('fieldInvalid', 'messages').replace('{field}', this.getLabelText());

        this.showValidationMessage(msg);

        return true;
    }
}
