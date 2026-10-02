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

import ArrayFieldView from 'views/fields/array';

export default class extends ArrayFieldView {

    // language=Handlebars
    detailTemplateContent = `
        {{#unless isEmpty}}
            {{#each items}}
                <div
                    class="multi-enum-item-container"
                    style="{{#if strikeThrough}} text-decoration: line-through; {{/if}}"
                >{{label}}{{#if required}} *{{/if}}</div>
            {{/each}}
        {{else}}
            {{#if valueIsSet}}<span class="none-value">{{translate 'None'}}</span>
            {{else}}
                <span class="loading-value"></span>
            {{/if}}
        {{/unless}}
    `

    /**
     * @private
     * @type {string[]}
     */
    webFormNotAllowedFields

    data() {
        /** @type {string[]|null} */
        const items = this.model.get(this.name);

        if (!items) {
            return super.data();
        }

        const dataItems = items.map(it => {
            return {
                label: this.translatedOptions[it] || it,
                strikeThrough: this.model.attributes.formEnabled && this.webFormNotAllowedFields.includes(it),
                required: this.isFieldRequired(it),
            };
        })

        return {
            ...super.data(),
            items: dataItems,
        };
    }

    getAttributeList() {
        return [...super.getAttributeList(), 'formEnabled', 'fieldParams'];
    }

    setup() {
        this.webFormNotAllowedFields = [];

        super.setup();

        this.listenTo(this.model, 'change:formEnabled', (m, v, o) => {
            if (!o.ui || !this.isDetailMode()) {
                return;
            }

            this.reRender();
        });

        this.addActionHandler('toggleRequired', (e, target) => this.toggleRequired(target.dataset.value));
    }

    setupOptions() {
        this.params.options = [];
        this.translatedOptions = {};

        /** @type {Record.<string, Record>} */
        const fields = this.getMetadata().get(['entityDefs', 'Lead', 'fields']) || {};

        /** @type {string[]} */
        const ignoreFieldList = this.getMetadata()
            .get(`entityDefs.LeadCapture.fields.fieldList.ignoreFieldList`) || [];

        /** @type {string[]} */
        const webFormTypeList = this.getMetadata()
            .get(`entityDefs.LeadCapture.fields.fieldList.webFormFieldTypeList`) || [];

        for (const field in fields) {
            const defs = fields[field];

            if (defs.disabled || defs.utility || defs.readOnly) {
                continue;
            }

            if (ignoreFieldList.includes(field)) {
                continue;
            }

            if (!webFormTypeList.includes(defs.type)) {
                this.webFormNotAllowedFields.push(field);
            }

            this.params.options.push(field);
            this.translatedOptions[field] = this.translate(field, 'fields', 'Lead');
        }
    }

    getItemHtml(value) {
        const html = super.getItemHtml(value);

        const div = document.createElement('div');
        div.innerHTML = html;

        /** @type {HTMLElement} */
        const item = div.querySelector('.list-group-item');

        const group = document.createElement('div');
        group.classList.add('btn-group', 'pull-right', 'item-button');

        const button = document.createElement('button');
        button.classList.add('btn', 'btn-link', 'btn-sm', 'dropdown-toggle');
        button.innerHTML = `<span class="caret"></span>`;
        button.dataset.toggle = 'dropdown';
        button.type = 'button';

        const ul = document.createElement('ul');
        ul.classList.add('dropdown-menu', 'pull-right');

        const li = document.createElement('li');
        const a = document.createElement('a');
        a.dataset.value = value;
        a.dataset.action = 'toggleRequired';

        a.role = 'button';
        a.tabIndex = 0;

        if (this.isFieldRequired(value)) {
            a.innerHTML += `<span class="check-icon fas fa-check pull-right"></span>`;
        }

        const textDiv = document.createElement('div');
        textDiv.textContent = this.translate('required', 'fields', 'Admin');
        a.append(textDiv);

        li.append(a);

        ul.append(li);

        group.append(button, ul);
        item.append(group);

        if (this.isFieldRequired(value)) {
            const text = div.querySelector('.text');

            if (text) {
                text.innerHTML += ' *';
            }
        }

        return div.innerHTML;
    }

    /**
     * @param {string} field
     * @return {boolean}
     */
    isFieldRequired(field) {
        const params = this.model.attributes.fieldParams || {};
        const fieldParams = params[field] || {};

        return !!fieldParams.required;
    }

    /**
     * @private
     * @param {string} field
     */
    toggleRequired(field) {
        const params = Espo.Utils.cloneDeep(this.model.attributes.fieldParams || {});

        if (!params[field]) {
            params[field] = {};
        }

        if (!('required' in params[field])) {
            params[field].required = false;
        }

        params[field].required = !params[field].required;

        const newParams = {};

        /** @type {string[]} */
        const fieldList = this.model.attributes.fieldList || [];

        fieldList.forEach(it => newParams[it] = params[it]);

        this.model.set('fieldParams', newParams, {ui: true});

        this.reRender();
    }

    addValue(value) {
        /** @type {string[]} */
        const items = this.model.get(this.name);

        let isAdded = false;

        if (items && !items.includes(value)) {
            isAdded = true;
        }

        super.addValue(value);

        if (
            isAdded &&
            this.getMetadata().get(`entityDefs.Lead.fields.${value}.required`) &&
            !this.isFieldRequired(value)
        ) {
            this.toggleRequired(value);
        }
    }
}
