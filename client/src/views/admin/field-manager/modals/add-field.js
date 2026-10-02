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

export default class extends ModalView {

    backdrop = true

    template = 'admin/field-manager/modals/add-field'

    data() {
        return {
            typeList: this.typeList,
        };
    }

    setup() {
        this.addActionHandler('addField', (e, target) => this.addField(target.dataset.type));

        this.addHandler('keyup', 'input[data-name="quick-search"]', (e, /** HTMLInputElement */target) => {
            this.processQuickSearch(target.value);
        });

        this.headerText = this.translate('Add Field', 'labels', 'Admin');

        this.typeList = [];

        /** @type {Record<string, Record>} */
        const fieldDefs = this.getMetadata().get('fields');

        Object.keys(this.getMetadata().get('fields')).forEach(type => {
            if (type in fieldDefs && !fieldDefs[type].notCreatable) {
                this.typeList.push(type);
            }
        });

        this.typeDataList = this.typeList.map(type => {
            return {
                type: type,
                label: this.translate(type, 'fieldTypes', 'Admin'),
            };
        });

        this.typeList.sort((v1, v2) => {
            return this.translate(v1, 'fieldTypes', 'Admin')
                .localeCompare(this.translate(v2, 'fieldTypes', 'Admin'));
        });
    }

    addField(type) {
        this.trigger('add-field', type);
        this.remove();
    }

    afterRender() {
        this.$noData = this.$el.find('.no-data');

        this.typeList.forEach(type => {
            let text = this.translate(type, 'fieldInfo', 'FieldManager');

            const $el = this.$el.find('a.info[data-name="' + type + '"]');

            if (text === type) {
                $el.addClass('hidden');

                return;
            }

            text = this.getHelper().transformMarkdownText(text, {linksInNewTab: true}).toString();

            Espo.Ui.popover($el, {
                content: text,
                placement: 'left',
            }, this);
        });

        setTimeout(() => this.$el.find('input[data-name="quick-search"]').focus(), 50);
    }

    processQuickSearch(text) {
        text = text.trim();

        const $noData = this.$noData;

        $noData.addClass('hidden');

        if (!text) {
            this.$el.find('ul .list-group-item').removeClass('hidden');

            return;
        }

        const matchedList = [];

        const lowerCaseText = text.toLowerCase();

        this.typeDataList.forEach(item => {
            const matched =
                item.label.toLowerCase().indexOf(lowerCaseText) === 0 ||
                item.type.toLowerCase().indexOf(lowerCaseText) === 0;

            if (matched) {
                matchedList.push(item.type);
            }
        });

        if (matchedList.length === 0) {
            this.$el.find('ul .list-group-item').addClass('hidden');

            $noData.removeClass('hidden');

            return;
        }

        this.typeDataList.forEach(item => {
            const $row = this.$el.find(`ul .list-group-item[data-name="${item.type}"]`);

            if (!~matchedList.indexOf(item.type)) {
                $row.addClass('hidden');

                return;
            }

            $row.removeClass('hidden');
        });
    }
}
