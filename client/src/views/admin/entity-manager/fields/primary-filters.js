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

class EntityManagerPrimaryFiltersFieldView extends ArrayFieldView {

    // language=Handlebars
    detailTemplateContent = `
        {{#unless isEmpty}}
            <table class="table table-bordered">
                <tbody>
                    {{#each dateList}}
                        <tr>
                            <td style="width: 42%">{{name}}</td>
                            <td style="width: 42%">{{label}}</td>
                            <td style="width: 16%; text-align: center;">
                                <a
                                    role="button"
                                    data-action="copyToClipboard"
                                    data-name="{{name}}"
                                    class="text-soft"
                                    title="{{translate 'Copy to Clipboard'}}"
                                ><span class="far fa-copy"></span></a>
                            </td>
                        </tr>
                    {{/each}}
                </tbody>
            </table>
        {{else}}
            {{#if valueIsSet}}
                <span class="none-value">{{translate 'None'}}</span>
            {{else}}
                <span class="loading-value"></span>
            {{/if}}
        {{/unless}}
    `

    // noinspection JSCheckFunctionSignatures
    data() {
        // noinspection JSValidateTypes
        return {
            ...super.data(),
            dateList: this.getValuesItems(),
        };
    }

    constructor(options) {
        super(options);

        this.targetEntityType = options.targetEntityType;
    }

    getValuesItems() {
        return (this.model.get(this.name) || []).map(/** string */item => {
            return {
                name: item,
                label: this.translate(item, 'presetFilters', this.targetEntityType),
            };
        });
    }

    setup() {
        super.setup();

        this.addActionHandler('copyToClipboard', (e, target) => this.copyToClipboard(target.dataset.name));
    }

    /**
     * @private
     * @param {string} name
     */
    copyToClipboard(name) {
        const urlPart = `#${this.targetEntityType}/list/primaryFilter=${name}`;

        navigator.clipboard.writeText(urlPart).then(() => {
            const msg = this.translate('urlHashCopiedToClipboard', 'messages', 'EntityManager')
                .replace('{name}', name);

            Espo.Ui.notify(msg, 'success', undefined, {closeButton: true});
        });
    }
}

export default EntityManagerPrimaryFiltersFieldView;
