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
import {inject} from 'di';
import ThemeManager from 'theme-manager';

export default class KanbanPipelineDropdownView extends View {

    // language=Handlebars
    templateContent = `
        <button
            class="btn btn-text dropdown-toggle btn-s-wide"
            data-toggle="dropdown"
            title="{{translate 'pipeline' category='fields'}}"
        >
            {{~#if 0}}{{/if~}}
            <span
                class="color-icon fas fa-square text-soft"
                style=" {{#if color}} color: {{color}} {{/if}} "
            ></span>
            <span>{{name}}</span>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            {{#each pipelines}}
                <li>
                    <a
                        role="button"
                        data-id="{{id}}"
                        data-action="selectPipeline"
                    >

                        {{#if selected}}
                            <span class="fas fa-check check-icon pull-right"></span>
                        {{/if}}
                        <div class="{{#if active}} text-bold text-soft{{/if}}">
                            {{~#if 0}}{{/if~}}
                            <span
                                class="color-icon fas fa-square text-soft"
                                style="
                                    {{#if color}} color: {{color}}; {{/if}}
                                    padding-right: var(--4px);
                                "
                            ></span>
                            {{name}}
                        </div>
                    </a>
                </li>
            {{/each}}
        </ul>
    `

    /**
     * @private
     * @type {ThemeManager}
     */
    @inject(ThemeManager)
    themeManager

    /**
     * @private
     * @type {Record<string, number|null>}
     */
    colorMap

    /**
     *
     * @param {{
     *     state: {
     *         pipelineId: string,
     *         pipelines: {
     *             id: string,
     *             name: string,
     *             color: number|null,
     *         }[],
     *     },
     *     onChange: function(string),
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;

        /** @private */
        this.state = options.state;
    }

    data() {
        const id = this.state.pipelineId;

        const name = this.state.pipelines.find(it => it.id === id)?.name;
        const color = id ? this.colorMap[id] : null;

        const pipelines = this.state.pipelines.map(it => ({
            ...it,
            selected: id === it.id,
            color: this.colorMap[it.id] ?? null,
        }));

        return {
            name: name ?? id,
            pipelines: pipelines,
            color: color ?? null,
        };
    }

    setup() {
        this.addActionHandler('selectPipeline', (e, target) => this.selectPipeline(target.dataset.id));

        /** @type {string[]} */
        const colors = this.themeManager.getParam('chartColorList') || [];

        this.colorMap = this.state.pipelines.reduce((o, it) => {
            let color = null;

            if (it.color != null) {
                color = colors[it.color] ?? null;
            }

            o[it.id] = color;

            return o;
        }, {});
    }

    /**
     * @private
     * @param {string} id
     */
    selectPipeline(id) {
        if (this.state.pipelineId === id) {
            return;
        }

        this.options.onChange(id);
    }
}
