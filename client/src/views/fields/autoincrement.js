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

import IntFieldView from 'views/fields/int';

class AutoincrementFieldView extends IntFieldView {

    type = 'autoincrement'

    validations = []

    inlineEditDisabled = true
    readOnly = true
    disableFormatting = true

    parse(value) {
        value = (value !== '') ? value : null;

        if (value !== null) {
            value = value.indexOf('.') !== -1 || value.indexOf(',') !== -1 ?
                NaN :
                parseInt(value);
        }

        return value;
    }

    fetch() {
        return {};
    }
}

export default AutoincrementFieldView;
