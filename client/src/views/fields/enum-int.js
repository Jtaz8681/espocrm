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

import EnumFieldView from 'views/fields/enum';

class EnumIntFieldView extends EnumFieldView {

    type = 'enumInt'

    listTemplate = 'fields/enum/list'
    detailTemplate = 'fields/enum/detail'
    editTemplate = 'fields/enum/edit'
    searchTemplate = 'fields/enum/search'

    validations = []

    setup() {
        super.setup();

        this.setupNumericTranslatedOptionsFallback();
    }

    /**
     * @private
     */
    setupNumericTranslatedOptionsFallback() {
        if (this.translatedOptions !== null) {
            return;
        }

        this.translatedOptions = {};

        (this.params.options || []).forEach(value => {
            if (
                value == null ||
                value === ''
            ) {
                return;
            }

            const valueStr = String(value);

            this.translatedOptions[valueStr] = valueStr;
        });
    }

    fetch() {
        const raw = this.$element.val();

        if (raw === '') {
            return {[this.name]: null};
        }

        const value = parseInt(raw);
        const data = {};

        data[this.name] = value;

        return data;
    }

    parseItemForSearch(item) {
        return parseInt(item);
    }
}

export default EnumIntFieldView;
