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

import BaseRecordView from 'views/record/base';

export default class extends BaseRecordView {

    template = 'admin/layouts/record/edit-attributes'

    /** @internal Important for dynamic logic working. */
    mode = 'edit'

    data() {
        return {
            attributeDataList: this.getAttributeDataList()
        };
    }

    getAttributeDataList() {
        const list = [];

        this.attributeList.forEach(attribute => {
            const defs = this.attributeDefs[attribute] || {};

            const type = defs.type;

            const isWide = !['enum', 'bool', 'int', 'float', 'varchar'].includes(type) &&
                attribute !== 'widthComplex';

            list.push({
                name: attribute,
                viewKey: attribute + 'Field',
                isWide: isWide,
                label: this.translate(defs.label || attribute, 'fields', 'LayoutManager'),
            });
        });

        return list;
    }

    setup() {
        super.setup();

        this.attributeList = this.options.attributeList || [];
        this.attributeDefs = this.options.attributeDefs || {};

        this.attributeList.forEach(field => {
            const params = this.attributeDefs[field] || {};
            const type = params.type || 'base';

            const viewName = params.view || this.getFieldManager().getViewName(type);

            this.createField(field, viewName, params);
        });
    }
}
