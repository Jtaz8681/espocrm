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

import LinkMultipleFieldView from 'views/fields/link-multiple';

export default class extends LinkMultipleFieldView {

    data() {
        const defaultAttributes = this.model.get('defaultAttributes') || {};

        const nameHash = defaultAttributes[this.options.field + 'Names'] || {};
        const idValues = defaultAttributes[this.options.field + 'Ids'] || [];

        const data = super.data();

        data.nameHash = nameHash;
        data.idValues = idValues;

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

        defaultAttributes[this.options.field + 'Ids'] = data[this.idsName];
        defaultAttributes[this.options.field + 'Names'] = data[this.nameHashName];

        if (data[this.idsName] === null || data[this.idsName].length === 0) {
            defaultAttributes = null;
        }

        return {
            defaultAttributes: defaultAttributes,
        };
    }

    copyValuesFromModel() {
        const defaultAttributes = this.model.get('defaultAttributes') || {};

        const idValues = defaultAttributes[this.options.field + 'Ids'] || [];
        const nameHash = defaultAttributes[this.options.field + 'Names'] || {};

        this.ids = idValues;
        this.nameHash = nameHash;
    }
}
