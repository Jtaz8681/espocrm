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
import ModelFactory from 'model-factory';

/**
 * @internal
 */
export default class AttachmentInsertSourceFromHelper {

    /**
     * @param {import('views/fields/attachment-multiple').default|import('views/fields/file').default} view
     */
    constructor(view) {
        /** @private */
        this.view = view;

        /** @private */
        this.model = view.model;
    }

    /**
     * @type {Metadata}
     * @private
     */
    @inject(Metadata)
    metadata

    /**
     * @type {ModelFactory}
     * @private
     */
    @inject(ModelFactory)
    modelFactory


    /**
     * @param {{
     *     source: string,
     *     onInsert: function(import('model').default[]),
     * }} params
     */
    insert(params) {
        const source = params.source;

        const viewName =
            this.metadata.get(['clientDefs', 'Attachment', 'sourceDefs', source, 'insertModalView']) ||
            this.metadata.get(['clientDefs', source, 'modalViews', 'select']) ||
            'views/modals/select-records';

        let filters = {};

        if (('getSelectFilters' + source) in this.view) {
            filters = this.view['getSelectFilters' + source]() || {};
        }

        // @todo EntityType => link mapping defined in metadata for automatic filtering.
        if (this.model.attributes.parentId && this.model.attributes.parentType === 'Account') {
            if (
                this.metadata.get(`entityDefs.${source}.fields.account.type`) === 'link' &&
                this.metadata.get(`entityDefs.${source}.links.account.entity`) === 'Account'
            ) {
                filters = {
                    account: {
                        type: 'equals',
                        attribute: 'accountId',
                        value: this.model.attributes.parentId,
                        data: {
                            type: 'is',
                            idValue: this.model.attributes.parentId,
                            nameValue: this.model.attributes.parentType,
                        },
                    },
                    ...filters,
                };
            }
        }

        let boolFilterList = this.metadata.get(`clientDefs.Attachment.sourceDefs.${source}.boolFilterList`);

        if (('getSelectBoolFilterList' + source) in this.view) {
            boolFilterList = this.view['getSelectBoolFilterList' + source]();
        }

        let primaryFilterName = this.metadata.get(`clientDefs.Attachment.sourceDefs.${source}.primaryFilter`);

        if (('getSelectPrimaryFilterName' + source) in this.view) {
            primaryFilterName = this.view['getSelectPrimaryFilterName' + source]();
        }

        /** @type {module:views/modals/select-records~Options} */
        const options = {
            entityType: source,
            createButton: false,
            filters: filters,
            boolFilterList: boolFilterList,
            primaryFilterName: primaryFilterName,
            multiple: true,
            onSelect: models => {
                models.forEach(async model => {
                    if (model.entityType === 'Attachment') {
                        params.onInsert([model]);

                        return;
                    }

                    /** @type {Record[]} */
                    const attachmentDataList = await Espo.Ajax.postRequest(`${source}/action/getAttachmentList`, {
                        id: model.id,
                        field: this.view.name,
                        parentType: this.view.entityType,
                    });

                    const attachmentSeed = await this.modelFactory.create('Attachment');

                    for (const item of attachmentDataList) {
                        const attachment = attachmentSeed.clone();

                        attachment.set(item);

                        params.onInsert([attachment]);
                    }
                });
            },
        };

        Espo.Ui.notifyWait();

        this.view.createView('modal', viewName, options, view => {
            view.render();

            Espo.Ui.notify();
        });
    }
}
