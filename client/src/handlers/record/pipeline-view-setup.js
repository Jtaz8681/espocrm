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
import {inject} from 'di';
import Metadata from 'metadata';
import AppParams from 'app-params';
import PipelinesHelper from 'helpers/misc/pipelines';

// noinspection JSUnusedGlobalSymbols
export default class PipelineViewSetupHandler {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @private
     * @type {import('model').default}
     */
    model

    /**
     * @private
     * @type {import('views/record/detail').default}
     */
    view

    /**
     * @private
     * @type {AppParams}
     */
    @inject(AppParams)
    appParams

    /**
     * @private
     * @type {{id: string, stages: {id: string, name: string, style: string|null}[]}[]}
     */
    pipelines

    /**
     * @param {import('views/record/detail').default} view
     */
    constructor(view) {
        this.view = view;
        this.model = view.model;
    }

    process() {
        const entityType = this.model.entityType;

        if (this.metadata.get(`scopes.${entityType}.pipelines`) !== true) {
            return;
        }

        this.pipelines = new PipelinesHelper().get(entityType);

        this.model.onChange({
            owner: this.view,
            attributes: ['pipelineId'],
            callback: o => {
                if (!o.ui) {
                    return;
                }

                setTimeout(() => this.updateStage(), 1);
            },
        });
    }

    /**
     * @private
     */
    updateStage() {
        const pipelineId = this.model.attributes.pipelineId;

        if (!pipelineId) {
            this.setStageNull();

            return;
        }

        const pipeline = this.pipelines.find(it => it.id === pipelineId);

        if (!pipeline) {
            this.setStageNull();

            return;
        }

        const stage = pipeline.stages[0];

        if (!stage) {
            this.setStageNull();

            return;
        }

        this.model.setMultiple({
            pipelineStageId: stage.id,
            pipelineStageName: stage.name,
        });

        this.model.trigger('pipeline-changed');
    }

    /**
     * @private
     */
    setStageNull() {
        this.model.setMultiple({
            pipelineStageId: null,
            pipelineStageName: null,
        });

        this.model.trigger('pipeline-changed');
    }
}
