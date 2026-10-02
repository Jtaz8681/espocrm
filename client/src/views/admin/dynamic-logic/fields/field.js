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

import MultiEnumFieldView from 'views/fields/multi-enum';
import MultiSelect from 'ui/multi-select';

export default class extends MultiEnumFieldView {

    getFieldList() {
        /** @type {Record<string, Record>} */
        const fields = this.getMetadata().get(`entityDefs.${this.options.scope}.fields`);

        const filterList = Object.keys(fields).filter(field => {
            const fieldType = fields[field].type || null;

            if (
                fields[field].disabled ||
                fields[field].utility
            ) {
                return;
            }

            if (!fieldType) {
                return;
            }

            if (!this.getMetadata().get(['clientDefs', 'DynamicLogic', 'fieldTypes', fieldType])) {
                return;
            }

            return true;
        });

        filterList.push('id');

        filterList.sort((v1, v2) => {
            return this.translate(v1, 'fields', this.options.scope)
                .localeCompare(this.translate(v2, 'fields', this.options.scope));
        });

        return filterList;
    }

    setupTranslatedOptions() {
        this.translatedOptions = {};

        this.params.options.forEach(item => {
            this.translatedOptions[item] = this.translate(item, 'fields', this.options.scope);
        });
    }

    setupOptions() {
        super.setupOptions();

        this.params.options = this.getFieldList();
        this.setupTranslatedOptions();
    }

    afterRender() {
        super.afterRender();

        if (this.$element) {
            MultiSelect.focus(this.$element);
        }
    }
}
