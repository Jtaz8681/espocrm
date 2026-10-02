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
import RecordModal from 'helpers/record-modal';

/**
 * @internal
 */
class CreateRelatedHelper {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @param {import('view').default} view
     */
    constructor(view) {
        /** @private */
        this.view = view;
    }

    /**
     * @param {import('model').default} model
     * @param {string} link
     * @param {{
     *     focusForCreate?: boolean,
     *     afterSave: function(import('model').default),
     * }} [options]
     * @return {Promise<import('views/modals/edit').default>}
     */
    async process(model, link, options = {}) {
        const scope = model.defs['links'][link].entity;
        const foreignLink = model.defs['links'][link].foreign;

        /** @type {Record} */
        const panelDefs = this.metadata.get(`clientDefs.${model.entityType}.relationshipPanels.${link}`) || {};

        const attributeMap = panelDefs.createAttributeMap || {};
        const handler = panelDefs.createHandler;

        let attributes = {};

        Object.keys(attributeMap).forEach(attr => attributes[attributeMap[attr]] = model.get(attr));

        if (handler) {
            const Handler = await Espo.loader.requirePromise(handler);
            /** @type {import('contracts/relation').CreateRelatedHandler} */
            const handlerObj = new Handler(this.view.getHelper(), {link: link});

            const additionalAttributes = await handlerObj.getAttributes(model, link);

            attributes = {...attributes, ...additionalAttributes};
        }

        const helper = new RecordModal();

        return await helper.showCreate(this.view, {
            entityType: scope,
            relate: {
                model: model,
                link: foreignLink,
            },
            attributes: attributes,
            focusForCreate: options.focusForCreate,
            afterSave: m => {
                if (options.afterSave) {
                    options.afterSave(m);
                }

                model.trigger(`update-related:${link}`);
                model.trigger('after:relate');
                model.trigger(`after:relate:${link}`);
            },
        });
    }
}

export default CreateRelatedHelper;
