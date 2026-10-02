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

class NumberFieldView extends VarcharFieldView {

    type = 'number'

    validations = []

    inlineEditDisabled = true
    readOnly = true

    data() {
        return {
            ...super.data(),
            textClass: 'numeric-text',
        };
    }

    /** @inheritDoc */
    fetch() {
        return {};
    }
}

export default NumberFieldView;
