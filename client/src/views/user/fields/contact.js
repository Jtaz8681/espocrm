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

import LinkFieldView from 'views/fields/link';

export default class extends LinkFieldView {

    select(model) {
        super.select(model);

        const attributes = {};

        if (model.get('accountId')) {
            const names = {};

            names[model.get('accountId')] = model.get('accountName');
            attributes.accountsIds = [model.get('accountId')];
            attributes.accountsNames = names;
        }

        attributes.firstName = model.get('firstName');
        attributes.lastName = model.get('lastName');
        attributes.salutationName = model.get('salutationName');

        attributes.emailAddress = model.get('emailAddress');
        attributes.emailAddressData = model.get('emailAddressData');

        attributes.phoneNumber = model.get('phoneNumber');
        attributes.phoneNumberData = model.get('phoneNumberData');

        if (this.model.isNew() && !this.model.get('userName') && attributes.emailAddress) {
            attributes.userName = attributes.emailAddress;
        }

        this.model.set(attributes);
    }
}
