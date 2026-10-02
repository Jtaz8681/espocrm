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

class EmailAddressFieldView extends VarcharFieldView {

    editTemplate = 'fields/email-address/edit'

    validations = ['required', 'emailAddress']

    emailAddressRe = new RegExp(
        /^[-!#$%&'*+/=?^_`{|}~A-Za-z0-9]+(?:\.[-!#$%&'*+/=?^_`{|}~A-Za-z0-9]+)*/.source +
        /@([A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9][A-Za-z0-9-]*[A-Za-z0-9]/.source
    )

    // noinspection JSUnusedGlobalSymbols
    validateEmailAddress() {
        const value = this.model.get(this.name);

        if (!value) {
            return false;
        }

        if (value !== '' && !this.emailAddressRe.test(value)) {
            const msg = this.translate('fieldShouldBeEmail', 'messages')
                .replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }

        return false;
    }
}

export default EmailAddressFieldView;
