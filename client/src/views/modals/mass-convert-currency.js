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

import ModalView from 'views/modal';
import Model from 'model';
import Helper from 'helpers/mass-action';

class MassConvertCurrencyModalView extends ModalView {

    template = 'modals/mass-convert-currency'

    className = 'dialog dialog-record'

    buttonList = [
        {
            name: 'cancel',
            label: 'Cancel',
        }
    ]

    data() {
        return {};
    }

    setup() {
        this.$header = $('<span>')
            .append(
                $('<span>').text(this.translate(this.options.entityType, 'scopeNamesPlural')),
                ' <span class="chevron-right"></span> ',
                $('<span>').text(this.translate('convertCurrency', 'massActions'))
            )

        this.addButton({
            name: 'convert',
            text: this.translate('Update'),
            style: 'danger'
        }, true);

        const model = this.model = new Model();

        model.set('currency', this.getConfig().get('defaultCurrency'));
        model.set('baseCurrency', this.getConfig().get('baseCurrency'));
        model.set('currencyRates', this.getConfig().get('currencyRates'));
        model.set('currencyList', this.getConfig().get('currencyList'));

        this.createView('currency', 'views/fields/enum', {
            model: model,
            params: {
                options: this.getConfig().get('currencyList')
            },
            name: 'currency',
            selector: '.field[data-name="currency"]',
            mode: 'edit',
            labelText: this.translate('Convert to')
        });

        this.createView('baseCurrency', 'views/fields/enum', {
            model: model,
            params: {
                options: this.getConfig().get('currencyList')
            },
            name: 'baseCurrency',
            selector: '.field[data-name="baseCurrency"]',
            mode: 'detail',
            labelText: this.translate('baseCurrency', 'fields', 'Settings'),
            readOnly: true
        });

        this.createView('currencyRates', 'views/currency/fields/currency-rates', {
            model: model,
            name: 'currencyRates',
            selector: '.field[data-name="currencyRates"]',
            mode: 'edit',
            labelText: this.translate('currencyRates', 'fields', 'Settings')
        });
    }

    /**
     * @param {string} field
     * @return {module:views/fields/base}
     */
    getFieldView(field) {
        return this.getView(field);
    }

    // noinspection JSUnusedGlobalSymbols
    actionConvert() {
        this.disableButton('convert');

        this.getFieldView('currency').fetchToModel();
        this.getFieldView('currencyRates').fetchToModel();

        const currency = this.model.get('currency');
        const currencyRates = this.model.get('currencyRates');

        const hasWhere = !this.options.ids || this.options.ids.length === 0;

        const helper = new Helper(this);

        const idle = hasWhere && helper.checkIsIdle(this.options.totalCount);

        Espo.Ajax.postRequest('MassAction', {
                entityType: this.options.entityType,
                action: 'convertCurrency',
                params: {
                   ids: this.options.ids || null,
                   where: hasWhere ? this.options.where : null,
                   searchParams: hasWhere ? this.options.searchParams : null,
                },
                data: {
                    fieldList: this.options.fieldList || null,
                    currency: currency,
                    targetCurrency: currency,
                    rates: currencyRates,
                },
                idle: idle,
            })
            .then(result => {
                if (result.id) {
                    helper
                        .process(result.id, 'convertCurrency')
                        .then(view => {
                            this.listenToOnce(view, 'close', () => this.close());

                            this.listenToOnce(view, 'success', result => {
                                this.trigger('after:update', {
                                    count: result.count,
                                    idle: true,
                                });
                            });
                        });

                    return;
                }

                this.trigger('after:update', {count: result.count});

                this.close();
            })
            .catch(() => {
                this.enableButton('convert');
            });
    }
}

export default MassConvertCurrencyModalView;
