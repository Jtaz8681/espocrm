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

class DuplicateFieldListCheckEntityManagerFieldView extends MultiEnumFieldView {

    fieldTypeList = [
        'varchar',
        'personName',
        'email',
        'phone',
        'url',
        'barcode',
    ]

    setupOptions() {
        let entityType = this.model.get('name');

        let options =
            this.getFieldManager()
                .getEntityTypeFieldList(entityType, {
                    typeList: this.fieldTypeList,
                    onlyAvailable: true,
                })
                .sort((a, b) => {
                    return this.getLanguage().translate(a, 'fields', this.entityType)
                        .localeCompare(
                            this.getLanguage().translate(b, 'fields', this.entityType)
                        );
                });

        this.translatedOptions = {};

        options.forEach(item => {
            this.translatedOptions[item] = this.translate(item, 'fields', entityType);
        })

        this.params.options = options;
    }
}

export default DuplicateFieldListCheckEntityManagerFieldView;
