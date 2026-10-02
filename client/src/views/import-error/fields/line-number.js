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

export default class extends IntFieldView {

    disableFormatting = true

    data() {
        const data = super.data();

        data.valueIsSet = this.model.has(this.sourceName);
        data.isNotEmpty = this.model.has(this.sourceName);

        return data;
    }

    setup() {
        super.setup();

        this.sourceName = this.name === 'exportLineNumber' ?
            'exportRowIndex' :
            'rowIndex';
    }

    getAttributeList() {
        return [this.sourceName];
    }

    getValueForDisplay() {
        let value = this.model.get(this.sourceName);

        value++;

        return this.formatNumber(value);
    }
}
