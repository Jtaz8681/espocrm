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

class PersonalDataRecordView extends BaseRecordView {

    template = 'personal-data/record/record'

    additionalEvents = {
        /** @this PersonalDataRecordView */
        'click .checkbox': function (e) {
            const name = $(e.currentTarget).data('name');

            if (e.currentTarget.checked) {
                if (!~this.checkedFieldList.indexOf(name)) {
                    this.checkedFieldList.push(name);
                }

                if (this.checkedFieldList.length === this.fieldList.length) {
                    this.$el.find('.checkbox-all').prop('checked', true);
                } else {
                    this.$el.find('.checkbox-all').prop('checked', false);
                }
            } else {
                const index = this.checkedFieldList.indexOf(name);

                if (~index) {
                    this.checkedFieldList.splice(index, 1);
                }

                this.$el.find('.checkbox-all').prop('checked', false);
            }

            this.trigger('check', this.checkedFieldList);
        },
        /** @this PersonalDataRecordView */
        'click .checkbox-all': function (e) {
            if (e.currentTarget.checked) {
                this.checkedFieldList = Espo.Utils.clone(this.fieldList);

                this.$el.find('.checkbox').prop('checked', true);
            } else {
                this.checkedFieldList = [];

                this.$el.find('.checkbox').prop('checked', false);
            }

            this.trigger('check', this.checkedFieldList);
        },
    }

    checkedFieldList

    data() {
        const data = {};

        data.fieldDataList = this.getFieldDataList();
        data.scope = this.scope;
        data.editAccess = this.editAccess;

        return data;
    }

    setup() {
        super.setup();

        this.events = {
            ...this.additionalEvents,
            ...this.events,
        };

        this.scope = this.model.entityType;

        this.fieldList = [];
        this.checkedFieldList = [];

        this.editAccess = this.getAcl().check(this.model, 'edit');

        const fieldDefs = this.getMetadata().get(['entityDefs', this.scope, 'fields']) || {};

        const fieldList = [];

        for (const field in fieldDefs) {
            const defs = /** @type {Record} */fieldDefs[field];

            if (defs.isPersonalData) {
                fieldList.push(field);
            }
        }

        fieldList.forEach(field => {
            const type = fieldDefs[field].type;
            const attributeList = this.getFieldManager().getActualAttributeList(type, field);

            let isNotEmpty = false;

            attributeList.forEach(attribute => {
                const value = this.model.get(attribute);

                if (value) {
                    if (Object.prototype.toString.call(value) === '[object Array]') {
                        if (!value.length) {
                            return;
                        }
                    }

                    isNotEmpty = true;
                }
            });

            const hasAccess = !this.getAcl().getScopeForbiddenFieldList(this.scope).includes(field);

            if (isNotEmpty && hasAccess) {
                this.fieldList.push(field);
            }
        });

        this.fieldList = this.fieldList.sort((v1, v2) => {
            return this.translate(v1, 'fields', this.scope)
                .localeCompare(this.translate(v2, 'fields', this.scope));
        });

        this.fieldList.forEach(field => {
            this.createField(field, null, null, 'detail', true);
        });
    }

    getFieldDataList() {
        const forbiddenList = this.getAcl().getScopeForbiddenFieldList(this.scope, 'edit');

        const list = [];

        this.fieldList.forEach(field => {
            list.push({
                name: field,
                key: field + 'Field',
                editAccess: this.editAccess && !~forbiddenList.indexOf(field),
            });
        });

        return list;
    }
}

export default PersonalDataRecordView;
