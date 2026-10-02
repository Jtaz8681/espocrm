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

class ComplexCreatedFieldView extends BaseFieldView {

    // language=Handlebars
    detailTemplateContent =  `
        {{~#if hasAt~}}
            <span data-name="{{baseName}}At" class="field">{{{atField}}}</span>
        {{~/if~}}
        {{~#if hasBoth~}}
            <span style="user-select: none"> <span class="text-muted middle-dot"></span> </span>
        {{~/if~}}
        {{~#if hasBy~}}
            <span data-name="{{baseName}}By" class="field">{{{byField}}}</span>
        {{~/if~}}
    `

    baseName = 'created'

    getAttributeList() {
        return [this.fieldAt, this.fieldBy];
    }

    init() {
        this.baseName = this.options.baseName || this.baseName;
        this.fieldAt = this.baseName + 'At';
        this.fieldBy = this.baseName + 'By';

        super.init();
    }

    setup() {
        super.setup();

        this.createField('at');
        this.createField('by');
    }

    // noinspection JSCheckFunctionSignatures
    data() {
        const hasBy = this.model.has(this.fieldBy + 'Id');
        const hasAt = this.model.has(this.fieldAt);

        return {
            baseName: this.baseName,
            hasBy: hasBy,
            hasAt: hasAt,
            hasBoth: hasAt && hasBy,
            ...super.data(),
        };
    }

    createField(part) {
        const field = this.baseName + Espo.Utils.upperCaseFirst(part);

        const type = this.model.getFieldType(field) || 'base';

        const viewName = this.model.getFieldParam(field, 'view') ||
            this.getFieldManager().getViewName(type);

        this.createView(part + 'Field', viewName, {
            name: field,
            model: this.model,
            mode: this.MODE_DETAIL,
            readOnly: true,
            readOnlyLocked: true,
            selector: '[data-name="' + field + '"]',
            isInComplexField: true,
        });
    }

    fetch() {
        return {};
    }
}

// noinspection JSUnusedGlobalSymbols
export default ComplexCreatedFieldView;
