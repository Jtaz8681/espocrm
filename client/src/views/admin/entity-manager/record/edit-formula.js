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

class EntityManagerEditFormulaRecordView extends BaseRecordView {

    template = 'admin/entity-manager/record/edit-formula'

    data() {
        return {
            field: this.field,
            fieldKey: this.field + 'Field',
        };
    }

    setup() {
        super.setup();

        this.field = this.options.type;

        let additionalFunctionDataList = null;

        if (this.options.type === 'beforeSaveApiScript') {
            additionalFunctionDataList = this.getRecordServiceFunctionDataList();
        } else if (this.options.type === 'beforeSaveCustomScript') {
            additionalFunctionDataList = this.getBeforeSaveFunctionDataList();
        }

        this.createField(
            this.field,
            'views/fields/formula',
            {
                targetEntityType: this.options.targetEntityType,
                height: 504,
            },
            'edit',
            false,
            {additionalFunctionDataList: additionalFunctionDataList}
        );
    }

    getRecordServiceFunctionDataList() {
        return [
            {
                name: 'recordService\\skipDuplicateCheck',
                insertText: 'recordService\\skipDuplicateCheck()',
                returnType: 'bool'
            },
            {
                name: 'recordService\\throwDuplicateConflict',
                insertText: 'recordService\\throwDuplicateConflict(RECORD_ID)',
            },
            {
                name: 'recordService\\throwBadRequest',
                insertText: 'recordService\\throwBadRequest(MESSAGE)',
            },
            {
                name: 'recordService\\throwForbidden',
                insertText: 'recordService\\throwForbidden(MESSAGE)',
            },
            {
                name: 'recordService\\throwConflict',
                insertText: 'recordService\\throwConflict(MESSAGE)',
            },
        ];
    }

    /**
     * @private
     * @return {Record[]}
     */
    getBeforeSaveFunctionDataList() {
        return [
            {
                name: 'exception\\throwInvalid',
                insertText: 'exception\\throwInvalid()',
            },
        ];
    }
}

export default EntityManagerEditFormulaRecordView;
