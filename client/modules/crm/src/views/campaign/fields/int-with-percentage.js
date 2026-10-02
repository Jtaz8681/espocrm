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

// noinspection JSUnusedGlobalSymbols
export default class extends IntFieldView {

    setup() {
        this.percentageField = this.name.substr(0, this.name.length - 5) + 'Percentage';

        super.setup();
    }

    getAttributeList() {
        const list = super.getAttributeList();

        if (this.model.hasField(this.percentageField)) {
            list.push(this.percentageField);
        }

        return list;
    }

    getValueForDisplay() {
        const percentageFieldName = this.percentageField;

        let value = this.model.get(this.name);
        const percentageValue = this.model.get(percentageFieldName);

        if (
            percentageValue != null &&
            percentageValue
        ) {
            value += ' ' + '(' + this.model.get(percentageFieldName) + '%)';
        }

        return value;
    }
}
