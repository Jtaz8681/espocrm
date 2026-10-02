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

class ValidationFailuresFieldView extends BaseFieldView {

    // language=Handlebars
    detailTemplateContent = `
        {{#if itemList.length}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 50%;">{{translate 'Field'}}</th>
                    <th>{{translateOption 'Validation' scope='ImportError' field='type'}}</th>
                </tr>
            </thead>
            <tbody>
                {{#each itemList}}
                <tr>
                    <td>{{translate field category='fields' scope=entityType}}</td>
                    <td>
                        {{translate type category='fieldValidations'}}
                        {{#if popoverText}}
                        <a
                            role="button"
                            tabindex="-1"
                            class="text-danger popover-anchor"
                            data-text="{{popoverText}}"
                        ><span class="fas fa-info-circle"></span></a>
                        {{/if}}
                    </td>
                </tr>
                {{/each}}
            </tbody>
        </table>
        {{else}}
        <span class="none-value">{{translate 'None'}}</span>
        {{/if}}
    `

    data() {
        const data = super.data();

        data.itemList = this.getDataList();

        return data;
    }

    afterRenderDetail() {
        this.$el.find('.popover-anchor').each((i, /** HTMLElement */el) => {
            const text = this.getHelper().transformMarkdownText(el.dataset.text).toString();

            Espo.Ui.popover($(el), {content: text}, this);
        });
    }

    /**
     * @return {Object[]}
     */
    getDataList() {
        const itemList = Espo.Utils.cloneDeep(this.model.get(this.name)) || [];

        const entityType = this.model.get('entityType');

        if (Array.isArray(itemList)) {
            itemList.forEach(item => {
                const fieldManager = this.getFieldManager();
                const language = this.getLanguage();

                const fieldType = fieldManager.getEntityTypeFieldParam(entityType, item.field, 'type');

                if (!fieldType) {
                    return;
                }

                const key = fieldType + '_' + item.type;

                if (!language.has(key, 'fieldValidationExplanations', 'Global')) {
                    if (!language.has(item.type, 'fieldValidationExplanations', 'Global')) {
                        return;
                    }

                    item.popoverText = language.translate(item.type, 'fieldValidationExplanations');

                    return;
                }

                item.popoverText = language.translate(key, 'fieldValidationExplanations');
            });
        }

        return itemList;
    }
}

// noinspection JSUnusedGlobalSymbols
export default ValidationFailuresFieldView;
