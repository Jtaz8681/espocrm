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

import Controller from 'controller';

export default class ActivitiesController extends Controller {

    checkAccess(action) {
        return this.getAcl().check('Activities');
    }

    // noinspection JSUnusedGlobalSymbols
    actionActivities(options) {
        this.processList('activities', options.entityType, options.id, options.targetEntityType);
    }

    actionHistory(options) {
        this.processList('history', options.entityType, options.id, options.targetEntityType);
    }

    /**
     * @private
     * @param {'activities'|'history'} type
     * @param {string} entityType
     * @param {string} id
     * @param {string} targetEntityType
     */
    async processList(type, entityType, id, targetEntityType) {
        const viewName = 'modules/crm/views/activities/list';

        const model = await this.modelFactory.create(entityType)

        model.id = id;

        await model.fetch({main: true});

        const collection = await this.collectionFactory.create(targetEntityType);
        collection.url = `Activities/${model.entityType}/${id}/${type}/list/${targetEntityType}`;

        this.main(viewName, {
            scope: entityType,
            model: model,
            collection: collection,
            link:  `${type}_${targetEntityType}`,
            type: type,
        });
    }
}
