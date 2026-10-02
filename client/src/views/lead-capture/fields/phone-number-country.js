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

import EnumFieldView from 'views/fields/enum';
// noinspection NpmUsedModulesInstalled
import intlTelInputGlobals from 'intl-tel-input-globals';

class LeadCapturePhoneNumberCountry extends EnumFieldView {

    setupOptions() {
        this.params.options = ['', ...intlTelInputGlobals.getCountryData().map(item => item.iso2)];

        this.translatedOptions = intlTelInputGlobals.getCountryData()
            .reduce((map, item) => {
                map[item.iso2] = `${item.iso2.toUpperCase()} +${item.dialCode}`;

                return map;
            }, {});
    }
}

export default LeadCapturePhoneNumberCountry;
