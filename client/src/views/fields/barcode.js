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

import VarcharFieldView from 'views/fields/varchar';

let JsBarcode;
let QRCode;

class BarcodeFieldView extends VarcharFieldView {

    type = 'barcode'

    listTemplate = 'fields/barcode/detail'
    detailTemplate = 'fields/barcode/detail'

    setup() {
        this.validations.push('valid');

        let maxLength = 255;

        // noinspection SpellCheckingInspection
        switch (this.params.codeType) {
            case 'EAN2':
                maxLength = 2; break;
            case 'EAN5':
                maxLength = 5; break;
            case 'EAN8':
                maxLength = 8; break;
            case 'EAN13':
                maxLength = 13; break;
            case 'UPC':
                maxLength = 12; break;
            case 'UPCE':
                maxLength = 11; break;
            case 'ITF14':
                maxLength = 14; break;
            case 'pharmacode':
                maxLength = 6; break;
        }

        this.params.maxLength = maxLength;

        // noinspection SpellCheckingInspection
        if (this.params.codeType !== 'QRcode') {
            this.isSvg = true;

            this.wait(
                Espo.loader.requirePromise('lib!jsbarcode')
                    .then(lib => JsBarcode = lib)
            );
        }
        else {
            this.wait(
                Espo.loader.requirePromise('lib!qrcodejs')
                    .then(lib => QRCode = lib)
            );
        }

        super.setup();

        $(window).on('resize.' + this.cid, () => {
            if (!this.isRendered()) {
                return;
            }

            this.controlWidth();
        });

        this.listenTo(this.recordHelper, 'panel-show', () => this.controlWidth());
    }


    data() {
        const data = super.data();

        data.isSvg = this.isSvg;

        // noinspection JSValidateTypes
        return data;
    }

    onRemove() {
        $(window).off('resize.' + this.cid);
    }

    afterRender() {
        super.afterRender();

        if (this.isListMode() || this.isDetailMode()) {
            const value = this.model.get(this.name);

            if (value) {
                // noinspection SpellCheckingInspection
                if (this.params.codeType === 'QRcode') {
                    this.initQrcode(value);
                }
                else {
                    const $barcode = $(this.getSelector() + ' .barcode');

                    if ($barcode.length) {
                        this.initBarcode(value);
                    }
                    else {
                        // SVG may be not available yet (in webkit).
                        setTimeout(() => {
                            this.initBarcode(value);
                            this.controlWidth();
                        }, 100);
                    }

                }
            }

            this.controlWidth();
        }
    }

    initQrcode(value) {
        let size = 128;

        if (value.length > 192) {
            size *= 2;
        }

        if (this.isListMode()) {
            size = 64;
        }

        const containerWidth = this.$el.width();

        if (containerWidth < size && containerWidth) {
            size = containerWidth;
        }

        const $barcode = this.$el.find('.barcode');

        const init = (level) => {
            const options = {
                text: value,
                width: size,
                height: size,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: level || QRCode.CorrectLevel.H,
            };

            new QRCode($barcode.get(0), options);
        };

        try {
            init();
        }
        catch (e) {
            try {
                $barcode.empty();

                init(QRCode.CorrectLevel.L);
            }
            catch (e) {
                console.error(this.name + ': ' + e.message);
            }
        }
    }

    initBarcode(value) {
        const factor = this.getThemeManager().getFontSizeFactor();

        try {
            JsBarcode(this.getSelector() + ' .barcode', value, {
                format: this.params.codeType,
                height: 50 * factor,
                fontSize: 14 * factor,
                margin: 0,
                lastChar: this.params.lastChar,
            });
        }
        catch (e) {
            console.error(this.name, e);
        }
    }

    controlWidth() {
        this.$el.find('.barcode').css('max-width', this.$el.width() + 'px');
    }

    // noinspection JSUnusedGlobalSymbols
    validateValid() {
        if (this.params.codeType === 'QRcode') {
            return;
        }

        const value = this.model.get(this.name);

        if (!value) {
            return;
        }

        let isValid;

        try {
            JsBarcode({}, value, {
                format: this.params.codeType,
                lastChar: this.params.lastChar,
                valid: valid => isValid = valid,
            });
        }
        catch (e) {
            return true;
        }

        if (isValid) {
            return;
        }

        const msg = this.translate('barcodeInvalid', 'messages')
            .replace('{field}', this.getLabelText())
            .replace('{type}', this.params.codeType);

        this.showValidationMessage(msg);

        return true;
    }
}

export default BarcodeFieldView;
