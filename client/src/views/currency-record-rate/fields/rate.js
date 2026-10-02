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

import DecimalFieldView from 'views/fields/decimal';

export default class CurrencyRecordRateRateFieldView extends DecimalFieldView {

    // language=Handlebars
    listTemplateContent = `
        {{#if isNotEmpty~}}
            <span class="text-soft">{{targetCode}} = </span>
            <span class="numeric-text">{{value}}</span>
            <span class="text-soft">{{baseCode}}</span>
        {{~/if~}}
    `

    // language=Handlebars
    detailTemplateContent = `
        {{~#if isNotEmpty~}}
            <span class="text-soft">{{targetCode}} = </span>
            <span class="numeric-text">{{value}}</span>
            <span class="text-soft">{{baseCode}}</span>
        {{~else~}}
            {{~#if valueIsSet~}}
                <span class="none-value">{{translate 'None'}}</span>
            {{~else~}}<span class="loading-value"></span>
            {{~/if}}
        {{~/if~}}
    `

    // language=Handlebars
    editTemplateContent = `
            <div class="input-group">
            <span class="input-group-addon radius-left" style="width: 24%">1 {{targetCode}} = </span>
            <span class="input-group-item">
                <input
                    type="text"
                    class="main-element form-control numeric-text"
                    data-name="{{name}}"
                    value="{{value}}"
                    autocomplete="espo-{{name}}"
                    pattern="[\\-]?[0-9]*"
                    style="text-align: end;"
                >
            </span>
            <span class="input-group-addon radius-right" style="width: 21%">{{baseCode}}</span>
        </div>
    `

    getAttributeList() {
        return [
            ...super.getAttributeList(),
            'baseCode',
            'recordName',
        ];
    }

    data() {
        let baseCode = this.model.attributes.baseCode;
        let targetCode = this.model.attributes.recordName;

        if (this.model.entityType === 'CurrencyRecord') {
            baseCode = this.getConfig().get('baseCurrency');
            targetCode = this.model.attributes.code;
        }

        return {
            ...super.data(),
            baseCode,
            targetCode,
        }
    }
}
