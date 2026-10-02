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

/**
 * @since 10.0.0
 */
class ColorFieldView extends BaseFieldView {
    // language=Handlebars
    listTemplateContent = `
        {{#if isNotNull}}
            <span class="fas fa-square" style="color: {{color}}"></span>
        {{/if}}
    `

    // language=Handlebars
    detailTemplateContent = `
        {{#if isNotNull}}
            <span class="fas fa-square" style="color: {{color}}"></span>
        {{else}}
            {{#if valueIsSet}}
                <span class="none-value">{{translate 'None'}}</span>
            {{else}}
                <span class="loading-value"></span>
            {{/if}}
        {{/if}}
    `

    // language=Handlebars
    editTemplateContent = `
        <div class="btn-group">
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span class="fas fa-square" style="color: {{color}}"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <ul
                        style="
                            display: grid;
                            grid-template-columns: 52px 52px 52px;
                            padding-left: 0;
                        "
                    >
                        {{#each colors}}
                            <li style="list-style: none;">
                                <a
                                    style="
                                        width: 100%;
                                        display: inline-block;
                                        text-align: center;
                                        padding: 2px;
                                    "
                                    class="dropdown-item"
                                    role="button"
                                    tabindex="0"
                                    data-action="selectColor{{#if selected}} active{{/if}}"
                                    data-value="{{value}}"
                                ><span class="fas fa-square" style="color: {{color}}"></span></a>
                            </li>
                        {{/each}}
                    </ul>
                </li>
                <li class="divider"></li>
                <li>
                    <a
                        class="dropdown-item"
                        role="button"
                        tabindex="0"
                        data-action="selectColor{{#if isNull}} active{{/if}}"
                        data-value=""
                        style="text-align: center;"
                    >{{translate 'None'}}</a>
                </li>
            </ul>
        </div>
    `

    // noinspection JSCheckFunctionSignatures
    data() {
        const value = this.model.attributes[this.name];

        if (this.isEditMode()) {
            const colors = [];

            let color = 'transparent';

            for (let i = 0; i < 9; i++) {
                colors.push({
                    color: this.colors[i],
                    selected: i === value,
                    value: i,
                });

                if (i === value) {
                    color = this.colors[i];
                }
            }

            return {
                colors: colors,
                color: color,
                isNull: value === null,
            };
        }

        if (this.isReadMode()) {
            return {
                valueIsSet: value !== undefined,
                isNotNull: value !== null,
                color: value != null ? this.colors[value] : 'transparent',
            };
        }

        return super.data();
    }

    setup() {
        /** @type {string[]} */
        this.colors = this.getHelper().themeManager.getParam('chartColorList') || [];

        this.addActionHandler('selectColor', (e, element) => {
            const value = element.dataset.value !== '' ?
                parseInt(element.dataset.value) : null;

            this.model.set(this.name, value, {ui: true});

            this.reRender();
        });
    }
}

export default ColorFieldView;
