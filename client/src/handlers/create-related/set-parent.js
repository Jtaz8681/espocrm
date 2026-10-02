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

import CreateRelatedHandler from 'handlers/create-related';
import {inject} from 'di';
import ModelFactory from 'model-factory';

export default class SetParentHandler extends CreateRelatedHandler {

    /**
     * @private
     * @type {ModelFactory}
     */
    @inject(ModelFactory)
    modelFactory

    async getAttributes(model, link) {
        const entityType = model.getLinkParam(link, 'entity');

        if (!entityType) {
            return {};
        }

        const seed = await this.modelFactory.create(entityType);

        /** @type {string[]} */
        const parentEntityTypeList = seed.getFieldParam('parent', 'entityList') ?? [];

        if (!parentEntityTypeList.includes(model.entityType)) {
            return {};
        }

        return {
            parentId: model.id,
            parentName: model.attributes.name,
            parentType: model.entityType,
        }
    }
}

