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

import View from 'view';

class LabelManagerCategoryView extends View {

    template = 'admin/label-manager/category'

    events = {}

    data() {
        return {
            categoryDataList: this.getCategoryDataList(),
        };
    }

    setup() {
        this.scope = this.options.scope;
        this.language = this.options.language;
        this.categoryData = this.options.categoryData;
    }

    getCategoryDataList() {
        const labelList = Object.keys(this.categoryData);

        labelList.sort((v1, v2) => {
            return v1.localeCompare(v2);
        });

        const categoryDataList = [];

        labelList.forEach(name => {
            let value = this.categoryData[name];

            if (value === null) {
                value = '';
            }

            if (value.replace) {
                value = value.replace(/\n/i, '\\n');
            }

            const o = {
                name: name,
                value: value,
            };

            const arr = name.split('[.]');

            o.label = arr.slice(1).join(' . ');

            categoryDataList.push(o);
        });

        return categoryDataList;
    }
}

export default LabelManagerCategoryView;
