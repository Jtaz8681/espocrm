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

import EnumFieldView from 'views/fields/enum';

export default class extends EnumFieldView {

    /**
     * @private
     * @type {string}
     */
    dataUrl = 'LeadCapture/action/smtpAccountDataList'

    getAttributeList() {
        return [this.name, 'inboundEmailId'];
    }

    data() {
        const data = super.data();

        data.valueIsSet = this.model.has('inboundEmailId');
        data.isNotEmpty = this.model.has('inboundEmailId');

        data.value = this.getValueForDisplay();

        data.valueTranslated = data.value != null ? this.translatedOptions[data.value] : undefined;

        return data;
    }

    setupOptions() {
        super.setupOptions();

        this.params.options = [];
        this.translatedOptions = {};

        this.params.options.push('');

        if (!this.loadedOptionList) {
            if (this.model.get('inboundEmailId')) {
                const item = 'inboundEmail:' + this.model.get('inboundEmailId');

                this.params.options.push(item);

                this.translatedOptions[item] =
                    (this.model.get('inboundEmailName') || this.model.get('inboundEmailId')) +
                    ' (' + this.translate('group', 'labels', 'MassEmail') + ')';
            }
        } else {
            this.loadedOptionList.forEach(item => {
                this.params.options.push(item);

                this.translatedOptions[item] =
                    (this.loadedOptionTranslations[item] || item) +
                    ' (' + this.translate('group', 'labels', 'MassEmail') + ')';
            });
        }

        this.translatedOptions[''] =
            this.getConfig().get('outboundEmailFromAddress') +
            ' (' + this.translate('system', 'labels', 'MassEmail') + ')';
    }

    getValueForDisplay() {
        if (!this.model.has(this.name)) {
            if (this.model.has('inboundEmailId')) {
                if (this.model.get('inboundEmailId')) {
                    return 'inboundEmail:' + this.model.get('inboundEmailId');
                } else {
                    return '';
                }
            } else {
                return '';
            }
        }

        return this.model.get(this.name);
    }

    setup() {
        super.setup();

        if (
            this.getAcl().checkScope('MassEmail', 'create') ||
            this.getAcl().checkScope('MassEmail', 'edit')
        ) {
            Espo.Ajax.getRequest(this.dataUrl).then(dataList => {
                if (!dataList.length) {
                    return;
                }

                this.loadedOptionList = [];

                this.loadedOptionTranslations = {};
                this.loadedOptionAddresses = {};
                this.loadedOptionFromNames = {};

                dataList.forEach(item => {
                    this.loadedOptionList.push(item.key);

                    this.loadedOptionTranslations[item.key] = item.emailAddress;
                    this.loadedOptionAddresses[item.key] = item.emailAddress;
                    this.loadedOptionFromNames[item.key] = item.fromName || '';
                });

                this.setupOptions();
                this.reRender();
            });
        }
    }

    fetch() {
        const data = {};
        const value = this.$element.val();

        data[this.name] = value;

        if (!value || value === '') {
            data.inboundEmailId = null;
            data.inboundEmailName = null;
        } else {
            const arr = value.split(':');

            if (arr.length > 1) {
                data.inboundEmailId = arr[1];
                data.inboundEmailName = this.translatedOptions[data.inboundEmailId] || data.inboundEmailId;
            }
        }

        return data;
    }
}
