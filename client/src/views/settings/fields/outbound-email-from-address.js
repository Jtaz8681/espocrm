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

import EmailAddressFieldView from 'views/fields/email-address';

class SettingsOutboundEmailFromAddressFieldView extends EmailAddressFieldView {

    useAutocompleteUrl = true

    getAutocompleteUrl(q) {
        return 'InboundEmail?searchParams=' + JSON.stringify({
            select: ['emailAddress'],
            maxSize: 7,
            where: [
                {
                    type: 'startsWith',
                    attribute: 'emailAddress',
                    value: q,
                },
                {
                    type: 'isTrue',
                    attribute: 'useSmtp',
                },
            ],
        });
    }

    transformAutocompleteResult(list) {
        const result = super.transformAutocompleteResult(list);

        result.forEach(item => {
            item.value = item.attributes.emailAddress;
        });

        return result;
    }
}

export default SettingsOutboundEmailFromAddressFieldView;
