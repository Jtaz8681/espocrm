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

import MassConvertCurrencyModalView from 'views/modals/mass-convert-currency';

class ConvertCurrencyModalView extends MassConvertCurrencyModalView {

    setup() {
        super.setup();

        this.headerText = this.translate('convertCurrency', 'massActions');
    }

    actionConvert() {
        this.disableButton('convert');

        this.getFieldView('currency').fetchToModel();
        this.getFieldView('currencyRates').fetchToModel();

        const currency = this.model.get('currency');
        const currencyRates = this.model.get('currencyRates');

        Espo.Ajax
            .postRequest('Action', {
                entityType: this.options.entityType,
                action: 'convertCurrency',
                id: this.options.model.id,
                data: {
                    targetCurrency: currency,
                    rates: currencyRates,
                    fieldList: this.options.fieldList || null,
                },
            })
            .then(attributes => {
                this.trigger('after:update', attributes);

                this.close();
            })
            .catch(() => {
                this.enableButton('convert');
            });
    }
}

export default ConvertCurrencyModalView;
