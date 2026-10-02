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

import MainView from 'views/main';

export default class AdminPipelinesMainView extends MainView {

    // language=Handlebars
    templateContent = `
        <div class="header page-header">{{{header}}}</div>
        <div class="record">
            {{#if entityTypeDataList.length}}
                <ul class="list-group list-group-panel">
                    {{#each entityTypeDataList}}
                        <li class="list-group-item">
                            <a href="{{link}}">{{label}}</a>
                        </li>
                    {{/each}}
                </ul>
            {{else}}
                <div class="panel panel-info">
                    <div class="panel-body">
                        {{complexText noMessage}}
                    </div>
                </div>
            {{/if}}
        </div>
    `

    /**
     * @private
     * @type {string[]}
     */
    entityTypeList

    data() {
        return {
            entityTypeDataList: this.getEntityTypeDataList(),
            noMessage: this.translate('noPipelinesEnabled', 'messages', 'Admin')
        }
    }

    setup() {
        this.createView('header', 'views/header', {});

        this.entityTypeList = this.getMetadata().getScopeEntityList()
            .filter(scope => this.getMetadata().get(`scopes.${scope}.pipelines`));
    }

    /**
     * @private
     * @return {Record[]}
     */
    getEntityTypeDataList() {
        return this.entityTypeList.map(it => {
            return {
                name: it,
                label: this.translate(it, 'scopeNamesPlural'),
                link: `#Admin/pipelines/scope=${it}`,
            };
        });
    }

    getHeader() {
        return this.buildHeaderHtml([
            (() => {
                const a = document.createElement('a');
                a.textContent = this.translate('Administration');
                a.href = '#Admin';

                return a;
            })(),
            (() => {
                const span = document.createElement('span');
                span.textContent = this.translate('Pipelines', 'labels', 'Admin');

                return span;
            })(),
        ]);
    }
}
