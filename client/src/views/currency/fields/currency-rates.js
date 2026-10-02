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

export default class extends BaseFieldView {

    editTemplateContent = `
        {{#each rateValues}}
            <div class="input-group">
                <span class="input-group-addon radius-left" style="width: 25%">1 {{@key}} = </span>
                <span class="input-group-item">
                    <input
                        class="form-control"
                        type="text"
                        data-currency="{{@key}}"
                        value="{{./this}}"
                        style="text-align: right;"
                    >
                </span>
                <span class="input-group-addon radius-right" style="width: 22%">{{../baseCurrency}}</span>
            </div>
        {{/each}}
    `

    data() {
        const baseCurrency = this.model.get('baseCurrency');
        const currencyRates = this.model.get('currencyRates') || {};

        const rateValues = {};

        (this.model.get('currencyList') || []).forEach(currency => {
            if (currency !== baseCurrency) {
                rateValues[currency] = currencyRates[currency];

                if (!rateValues[currency]) {
                    if (currencyRates[baseCurrency]) {
                        rateValues[currency] = Math.round(1 / currencyRates[baseCurrency] * 1000) / 1000;
                    }

                    if (!rateValues[currency]) {
                        rateValues[currency] = 1.00
                    }
                }
            }
        });

        return {
            rateValues: rateValues,
            baseCurrency: baseCurrency,
        };
    }

    fetch() {
        const data = {};
        const currencyRates = {};

        const baseCurrency = this.model.get('baseCurrency');

        const currencyList = this.model.get('currencyList') || [];

        currencyList.forEach(currency => {
            if (currency !== baseCurrency) {
                const value = this.$el.find(`input[data-currency="${currency}"]`).val() || '1';

                currencyRates[currency] = parseFloat(value);
            }
        });

        delete currencyRates[baseCurrency];

        for (const c in currencyRates) {
            if (!~currencyList.indexOf(c)) {
                delete currencyRates[c];
            }
        }

        data[this.name] = currencyRates;

        return data;
    }
}
