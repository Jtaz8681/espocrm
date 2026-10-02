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

import CurrencyFieldView from 'views/fields/currency';

class CurrencyConvertedFieldView extends CurrencyFieldView {

    data() {
        let data = super.data();

        const currencyValue = this.getConfig().get('defaultCurrency');

        data.currencyValue = currencyValue;
        data.currencySymbol = this.getMetadata().get(['app', 'currency', 'symbolMap', currencyValue]) || '';

        return data;
    }
}

export default CurrencyConvertedFieldView;
