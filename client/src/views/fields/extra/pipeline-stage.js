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

import LinkFieldView from 'views/fields/link';
import Model from 'model';
import EnumFieldView from 'views/fields/enum';
import PipelinesHelper from 'helpers/misc/pipelines';

export default class PipelineFieldView extends LinkFieldView {

    // language=Handlebars
    listTemplateContent = `
        {{#if stringValue}}
            <span
                class="label label-md label-state label-{{style}}"
                title="{{stringValue}}"
            >{{stringValue}}</span>
        {{/if}}
    `

    // language=Handlebars
    detailTemplateContent = `
        {{#if stringValue}}
            <span
                class="label label-md label-state label-{{style}}"
            >{{stringValue}}</span>
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
        <div data-sub-field="stage">{{{stageField}}}</div>
    `

    /**
     * @private
     * @type {{id: string, stages: {id: string, name: string, style: string|null}[]}[]}
     */
    pipelines

    data() {
        const data = super.data();

        data.stringValue = data.nameValue;

        const pipeline = this.getPipeline();

        data.style = 'default';

        if (pipeline && this.model.attributes[this.idName]) {
            const stage = pipeline.stages.find(it => it.id === this.model.attributes[this.idName]);

            data.style = stage?.style ?? 'default';
        }

        // noinspection JSValidateTypes
        return data;
    }

    setup() {
        super.setup();

        this.pipelines = new PipelinesHelper().get(this.entityType);

        this.setupSub();
    }

    /**
     * @private
     */
    setupSub() {
        this.subModel = new Model();

        const syncModels = () => {
            this.subModel.setMultiple({
                stage: this.model.attributes[this.idName],
            });
        };

        syncModels();

        this.listenTo(this.model, `change:${this.idName}`, (m, v, o) => {
            if (!o.ui || o.fromView && o.fromView !== this) {
                syncModels();
            }
        });

        this.listenTo(this.subModel, 'change', (m, o) => o.ui ? this.trigger('change') : null);

        this.listenTo(this.model, 'change:pipelineId pipeline-changed', async () => {
            await this.onEditModeSet()
            await this.reRender();
        });
    }

    /**
     * @private
     * @return {{id: string, stages: {id: string, name: string, style: string|null}[]}|null}
     */
    getPipeline() {
        const pipelineId = this.model.attributes.pipelineId;

        if (!pipelineId) {
            return null;
        }

        return this.pipelines.find(it => it.id === pipelineId) ?? null;
    }

    onEditModeSet() {
        const pipeline = this.getPipeline();

        const options = pipeline ? pipeline.stages.map(it => it.id) : [];

        const currentId = this.model.attributes[this.idName];

        if (!currentId || !pipeline) {
            options.unshift('');
        }

        if (currentId && !options.includes(currentId)) {
            options.push(currentId);
        }

        const translatedOptions = {};
        const style = {};

        if (pipeline) {
            pipeline.stages.forEach(it => {
                translatedOptions[it.id] = it.name;
                style[it.id] = it.style;
            });
        }

        if (currentId && !translatedOptions[currentId]) {
            translatedOptions[currentId] = this.model.attributes[this.idName] ?? currentId;
        }

        this.enumView = new EnumFieldView({
            name: 'stage',
            model: this.subModel,
            mode: 'edit',
            params: {
                options,
                translatedOptions,
                style,
            },
        });

        return this.assignView('stageField', this.enumView, '[data-sub-field="stage"]');
    }

    fetch() {
        const id = this.subModel.attributes.stage;
        let name = null;

        const pipeline = this.getPipeline();

        if (id && pipeline) {
            name = pipeline.stages.find(it => it.id === id)?.name ?? null;
        }

        // noinspection JSValidateTypes
        return {
            [this.idName]: id,
            [this.nameName]: name ?? id,
        };
    }
}
