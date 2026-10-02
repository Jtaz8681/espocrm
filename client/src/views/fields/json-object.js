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

class JsonObjectFieldView extends BaseFieldView {

    type = 'jsonObject'

    listTemplate = 'fields/json-object/detail'
    detailTemplate = 'fields/json-object/detail'

    data() {
        const data = super.data();

        data.valueIsSet = this.model.has(this.name);
        data.isNotEmpty = !!this.model.get(this.name);

        data.displayValue = null;

        if (data.value != null) {
            data.displayValue = '```\n' + data.value + '\n```';
        }

        return data;
    }

    getValueForDisplay() {
        const value = this.model.get(this.name);

        if (!value) {
            return null;
        }

        return JSON.stringify(value, null, 2);
    }
}

export default JsonObjectFieldView;

