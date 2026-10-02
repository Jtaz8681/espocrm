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

/** @module multi-collection */

import Collection from 'collection';
import _ from 'underscore';

/**
 * A collection that can contain entities of different entity types.
 */
class MultiCollection extends Collection {

    /**
     * A model seed map.
     *
     * @public
     * @type {Object.<string, module:model>}
     */
    seeds = null

    /** @inheritDoc */
    prepareAttributes(response, options) {
        if (Array.isArray(response)) {
            throw new Error("Bad response.");
        }

        this.total = response.total;

        if (!('list' in response)) {
            throw new Error("No 'list' in response.");
        }

        /** @type {({_scope?: string} & Object.<string, *>)[]} */
        const list = response.list;

        return list.map(attributes => {
            const entityType = attributes._scope;

            if (!entityType) {
                throw new Error("No '_scope' attribute.");
            }

            attributes = _.clone(attributes);
            delete attributes['_scope'];

            const model = this.seeds[entityType].clone();

            model.set(attributes);

            return model;
        });
    }

    /** @inheritDoc */
    clone(options) {
        const collection = super.clone(options);
        collection.seeds = this.seeds;

        return collection;
    }
}

// noinspection JSUnusedGlobalSymbols
export default MultiCollection;
