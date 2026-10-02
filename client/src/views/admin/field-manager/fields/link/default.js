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

    data() {
        const defaultAttributes = this.model.get('defaultAttributes') || {};
        const nameValue = defaultAttributes[this.options.field + 'Name'] || null;
        const idValue = defaultAttributes[this.options.field + 'Id'] || null;

        const data = super.data();

        data.nameValue = nameValue;
        data.idValue = idValue;

        return data;
    }

    setup() {
        super.setup();

        const entityType = this.options.scope;
        const field = this.options.field;

        this.foreignScope = this.getMetadata().get(['entityDefs', entityType, 'links', field, 'entity']) ??
            this.getMetadata().get(`entityDefs.${entityType}.fields.${field}.entity`);
    }

    fetch() {
        const data = super.fetch();

        let defaultAttributes = {};
        defaultAttributes[this.options.field + 'Id'] = data[this.idName];
        defaultAttributes[this.options.field + 'Name'] = data[this.nameName];

        if (data[this.idName] === null) {
            defaultAttributes = null;
        }

        return {
            defaultAttributes: defaultAttributes
        };
    }
}
