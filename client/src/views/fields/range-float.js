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

import RangeIntFieldView from 'views/fields/range-int';
import FloatFieldView from 'views/fields/float';

class RangeFloatFieldView extends RangeIntFieldView {

    type = 'rangeFloat'

    validations = ['required', 'float', 'range', 'order']
    decimalPlacesRawValue = 10

    setupAutoNumericOptions() {
        this.autoNumericOptions = {
            digitGroupSeparator: this.thousandSeparator || '',
            decimalCharacter: this.decimalMark,
            modifyValueOnWheel: false,
            selectOnFocus: false,
            decimalPlaces: this.decimalPlacesRawValue,
            decimalPlacesRawValue: this.decimalPlacesRawValue,
            allowDecimalPadding: false,
            showWarnings: false,
            formulaMode: true,
        };
    }

    // noinspection JSUnusedGlobalSymbols
    validateFloat() {
        const validate = (name) => {
            if (isNaN(this.model.get(name))) {
                const msg = this.translate('fieldShouldBeFloat', 'messages')
                    .replace('{field}', this.getLabelText());

                this.showValidationMessage(msg, '[data-name="' + name + '"]');

                return true;
            }
        };

        let result = false;

        result = validate(this.fromField) || result;
        result = validate(this.toField) || result;

        return result;
    }

    parse(value) {
        return FloatFieldView.prototype.parse.call(this, value);
    }

    formatNumber(value) {
        return FloatFieldView.prototype.formatNumberDetail.call(this, value);
    }
}

export default RangeFloatFieldView;

