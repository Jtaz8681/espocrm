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
import EnumFieldView from 'views/fields/enum';
import Model from 'model';
import FloatFieldView from 'views/fields/float';

class LayoutWidthComplexFieldView extends BaseFieldView {

    editTemplateContent = `
        <div class="row">
            <div data-name="value" class="col-sm-6">{{{value}}}</div>
            <div data-name="unit" class="col-sm-6">{{{unit}}}</div>
        </div>

    `
    getAttributeList() {
        return ['width', 'widthPx'];
    }

    setup() {
        this.auxModel =
            /**
             * @type {
             *     Model<{
             *         width: number|null,
             *         unit: string|null,
             *         value: number|null,
             *     }>
             * }
             */
            new Model();

        this.syncAuxModel();

        this.listenTo(this.model, 'change', (m, /** Record */o) => {
            if (o.ui) {
                return;
            }

            this.syncAuxModel();
        });

        const unitView = new EnumFieldView({
            name: 'unit',
            mode: 'edit',
            model: this.auxModel,
            params: {
                options: [
                    '%',
                    'px',
                ],
            },
        });

        const valueView = this.valueView = new FloatFieldView({
            name: 'value',
            mode: 'edit',
            model: this.auxModel,
            params: {
                min: this.getMinValue(),
                max: this.getMaxValue(),
            },
            labelText: this.translate('Value'),
        });

        this.assignView('unit', unitView, '[data-name="unit"]');
        this.assignView('value', valueView, '[data-name="value"]');

        this.listenTo(this.auxModel, 'change', (m, o) => {
            if (!o.ui) {
                return;
            }

            this.valueView.params.max = this.getMaxValue();
            this.valueView.params.min = this.getMinValue();

            this.model.set(this.fetch(), {ui: true});
        });
    }

    getMinValue() {
        return this.auxModel.attributes.unit === 'px' ? 30 : 5;
    }

    getMaxValue() {
        return this.auxModel.attributes.unit === 'px' ? 768 : 95;
    }

    validate() {
        return this.valueView.validate();
    }

    fetch() {
        if (this.auxModel.attributes.unit === 'px') {
            return {
                width: null,
                widthPx: this.auxModel.attributes.value,
            };
        }

        return {
            width: this.auxModel.attributes.value,
            widthPx: null,
        };
    }

    syncAuxModel() {
        const width = this.model.attributes.width;
        const widthPx = this.model.attributes.widthPx;

        const unit = width || !widthPx ? '%' : 'px';

        this.auxModel.setMultiple({
            unit: unit,
            value: unit === 'px' ? widthPx : width,
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default LayoutWidthComplexFieldView;
