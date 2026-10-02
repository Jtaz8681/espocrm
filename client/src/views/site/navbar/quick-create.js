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
import RecordModal from 'helpers/record-modal';

class QuickCreateNavbarView extends View {

    // language=Handlebars
    templateContent = `
        <a
            id="nav-quick-create-dropdown"
            class="dropdown-toggle"
            data-toggle="dropdown"
            role="button"
            tabindex="0"
            title="{{translate 'Create'}}"
        ><i class="fas fa-plus icon"></i></a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="nav-quick-create-dropdown">
            <li class="dropdown-header">{{translate 'Create'}}</li>
            {{#each list}}
                <li><a
                    href="#{{name}}/create"
                    data-name="{{name}}"
                    data-action="quickCreate"
                >
                    {{~#if iconClass~}}
                        <span
                            class="item-icon {{iconClass}}"
                            style="{{#if color}}color: {{color}};{{/if}}"
                        ></span>
                    {{~/if~}}
                    <span class="item-text">{{translate name category='scopeNames'}}</span>
                    {{~null~}}
                </a></li>
            {{/each}}
        </ul>
    `

    data() {
        return {
            list: this.list.map(it => {
                return {
                    name: it,
                    iconClass: this.getMetadata().get(`clientDefs.${it}.iconClass`),
                    color: this.getMetadata().get(`clientDefs.${it}.color`),
                };
            }),
        };
    }

    setup() {
        this.addActionHandler('quickCreate', (e, element) => {
            e.preventDefault();

            this.processCreate(element.dataset.name);
        });

        const scopes = this.getMetadata().get('scopes') || {};

        /** @type {string[]} */
        const list = this.getConfig().get('quickCreateList') || [];

        this.list = list.filter(scope => {
            if (!scopes[scope]) {
                return false;
            }

            if ((scopes[scope] || {}).disabled) {
                return;
            }

            if ((scopes[scope] || {}).acl) {
                return this.getAcl().check(scope, 'create');
            }

            return true;
        });
    }

    isAvailable() {
        return this.list.length > 0;
    }

    /**
     * @private
     * @param {string} scope
     */
    async processCreate(scope) {
        Espo.Ui.notifyWait();

        const type = this.getMetadata().get(`clientDefs.${scope}.quickCreateModalType`);

        if (type) {
            const viewName = this.getMetadata().get(`clientDefs.${scope}.modalViews.${type}`);

            if (viewName) {
                const view = await this.createView('modal', viewName , {scope: scope});

                await view.render();

                Espo.Ui.notify();

                return;
            }
        }

        const helper = new RecordModal();

        await helper.showCreate(this, {
            entityType: scope,
        });
    }
}

export default QuickCreateNavbarView;
