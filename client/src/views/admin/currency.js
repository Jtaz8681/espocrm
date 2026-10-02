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

import SettingsEditRecordView from 'views/settings/record/edit';
import EditView from 'views/edit';

export default class extends SettingsEditRecordView {

    layoutName = 'currency'

    saveAndContinueEditingAction = false

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:currencyList', (model, value, o) => {
            if (!o.ui) {
                return;
            }

            const currencyList = Espo.Utils.clone(model.get('currencyList'));

            this.setFieldOptionList('defaultCurrency', currencyList);
            this.setFieldOptionList('baseCurrency', currencyList);
        });

        this.whenReady().then(() => {
            const view = /** @type {EditView} view */
                this.getParentView();

            if (!view instanceof EditView) {
                return;
            }

            view.addMenuItem('buttons', {
                name: 'currencyRecords',
                link: '#CurrencyRecord/list/fromSettings=true',
                labelTranslation: 'Settings.labels.Currency Rates',
                iconClass: 'fas fa-euro-sign',
            });
        });
    }
}
