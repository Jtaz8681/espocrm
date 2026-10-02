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

import RecordController from 'controllers/record';
import TreeCollection from 'collections/tree';

class RecordTreeController extends RecordController {

    defaultAction = 'listTree'

    beforeView(options) {
        super.beforeView(options);

        options = options || {};

        if (options.model) {
            options.model.unset('childCollection');
            options.model.unset('childList');
        }
    }

    // noinspection JSUnusedGlobalSymbols
    beforeListTree() {
        this.handleCheckAccess('read');
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     *
     * @param {{
     *     currentId?: string,
     *     isReturn?: boolean,
     * }} options
     */
    async actionListTree(options) {
        const currentId = options.currentId;

        const collection = await this.getCollection();

        if (!(collection instanceof TreeCollection)) {
            throw new Error("Wrong collection.");
        }

        collection.url = `${collection.entityType}/action/listTree`;
        collection.currentId = currentId ?? null;

        const isReturn = options.isReturn || this.getRouter().backProcessed;

        this.main(this.getViewName('listTree'), {
            scope: this.name,
            collection: collection,
        }, undefined, {key: 'listTree', useStored: isReturn});
    }

    async create(options = {}) {
        if (options.parentId) {
            options.attributes ??= {};

            options.attributes.parentId = options.parentId;
            options.attributes.parentName = options.parentName;

            delete options.parentId;
            delete options.parentName;
        }

        return super.create(options);
    }
}

export default RecordTreeController;
