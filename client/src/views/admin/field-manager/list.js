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

import View from 'view';
import ViewDetailsModalView from 'views/admin/field-manager/modals/view-details';
import Ajax from 'ajax';
import Ui from 'ui';

class FieldManagerListView extends View {

    template = 'admin/field-manager/list'

    /**
     * @todo Implement. Set after a new field is created..
     *
     * @private
     * @type {Record<string, true>}
     */
    accentedFieldMap

    data() {
        return {
            scope: this.scope,
            fieldDefsArray: this.fieldDefsArray,
            typeList: this.typeList,
            hasAddField: this.hasAddField,
        };
    }

    events = {
        /** @this FieldManagerListView */
        'click [data-action="removeField"]': function (e) {
            const field = $(e.currentTarget).data('name');

            this.removeField(field);
        },
        /** @this FieldManagerListView */
        'keyup input[data-name="quick-search"]': function (e) {
            this.processQuickSearch(e.currentTarget.value);
        },
    }

    setup() {
        this.addActionHandler('viewDetails', (e, target) => this.viewDetails(target.dataset.name));

        this.accentedFieldMap = {};

        this.scope = this.options.scope;

        this.isCustomizable =
            !!this.getMetadata().get(`scopes.${this.scope}.customizable`) &&
            this.getMetadata().get(`scopes.${this.scope}.entityManager.fields`) !== false;

        this.hasAddField = true;

        const entityManagerData = this.getMetadata().get(['scopes', this.scope, 'entityManager']) || {};

        if ('addField' in entityManagerData) {
            this.hasAddField = entityManagerData.addField;
        }

        this.wait(
            this.buildFieldDefs()
        );
    }

    afterRender() {
        this.$noData = this.$el.find('.no-data');

        this.$el.find('input[data-name="quick-search"]').focus();
    }

    async buildFieldDefs() {
        const model = await this.getModelFactory().create(this.scope);

        this.fields = model.defs.fields;
        this.fieldList = Object.keys(this.fields).sort();
        this.fieldDefsArray = [];

        this.fieldList.forEach(field => {
            const defs = /** @type {Record} */ this.fields[field];

            this.fieldDefsArray.push({
                name: field,
                isCustom: defs.isCustom || false,
                type: defs.type,
                label: this.translate(field, 'fields', this.scope),
                isEditable: !defs.customizationDisabled &&
                    !defs.utility &&
                    this.isCustomizable,
                accented: this.accentedFieldMap[field] ?? false,
            });
        });

        this.fieldDefsArray = this.fieldDefsArray.sort((a, b) => {
            if (a.isEditable && !b.isEditable) {
                return -1;
            }

            if (!a.isEditable && b.isEditable) {
                return 1;
            }

            return 0;
        });
    }

    removeField(field) {
        const msg = this.translate('confirmRemove', 'messages', 'FieldManager')
            .replace('{field}', field);

        this.confirm(msg, () => {
            Ui.notifyWait();

            Ajax.deleteRequest('Admin/fieldManager/' + this.scope + '/' + field).then(() => {
                Ui.success(this.translate('Removed'));

                this.$el.find(`tr[data-name="${field}"]`).remove();

                this.getMetadata()
                    .loadSkipCache()
                    .then(() => {
                        this.buildFieldDefs()
                            .then(() => {
                                this.broadcastUpdate();

                                return this.reRender();
                            })
                            .then(() => Ui.success(this.translate('Removed')))
                    });
            });
        });
    }

    broadcastUpdate() {
        this.getHelper().broadcastChannel.postMessage('update:metadata');
        this.getHelper().broadcastChannel.postMessage('update:language');
    }

    processQuickSearch(text) {
        text = text.trim();

        const $noData = this.$noData;

        $noData.addClass('hidden');

        if (!text) {
            this.$el.find('table tr.field-row').removeClass('hidden');

            return;
        }

        const matchedList = [];

        const lowerCaseText = text.toLowerCase();

        this.fieldDefsArray.forEach(item => {
            let matched = false;

            if (
                item.label.toLowerCase().indexOf(lowerCaseText) === 0 ||
                item.name.toLowerCase().indexOf(lowerCaseText) === 0
            ) {
                matched = true;
            }

            if (!matched) {
                const wordList = item.label.split(' ')
                    .concat(
                        item.label.split(' ')
                    );

                wordList.forEach((word) => {
                    if (word.toLowerCase().indexOf(lowerCaseText) === 0) {
                        matched = true;
                    }
                });
            }

            if (matched) {
                matchedList.push(item.name);
            }
        });

        if (matchedList.length === 0) {
            this.$el.find('table tr.field-row').addClass('hidden');

            $noData.removeClass('hidden');

            return;
        }

        this.fieldDefsArray
            .map(item => item.name)
            .forEach(field => {
                const $row = this.$el.find(`table tr.field-row[data-name="${field}"]`);

                if (!~matchedList.indexOf(field)) {
                    $row.addClass('hidden');

                    return;
                }

                $row.removeClass('hidden');
            });
    }

    /**
     * @private
     * @param {string} name
     */
    async viewDetails(name) {
        const view = new ViewDetailsModalView({
            field: name,
            entityType: this.scope,
        });

        await this.assignView('modal', view);

        await view.render();
    }
}

export default FieldManagerListView;
