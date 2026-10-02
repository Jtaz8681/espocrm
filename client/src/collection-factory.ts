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

/** @module collection-factory */

import ModelFactory from 'model-factory';
import Settings from 'models/settings';
import Metadata from 'metadata';
import Collection from 'collection';
import Model from 'model';

/**
 * A collection factory.
 */
export default class CollectionFactory {

    private readonly recordListMaxSizeLimit: number

    /**
     * @param {module:model-factory} modelFactory
     * @param {module:models/settings} config
     * @param {module:metadata} metadata
     */
    constructor(
        private modelFactory: ModelFactory,
        config: Settings,
        private metadata: Metadata,
    ) {
        this.recordListMaxSizeLimit = config.get('recordListMaxSizeLimit') || 200;
    }

    /**
     * Create a collection.
     *
     * @param entityType An entity type.
     * @param [callback] Deprecated.
     * @param [context] Deprecated.
     * @returns A created collection.
     */
    create<T extends Model = Model>(
        entityType: string,
        callback?: Function, context?: object,
    ): Promise<Collection<T>> {

        return new Promise(resolve => {
            context = context || this;

            this.modelFactory.getSeed(entityType, Model => {
                const orderBy = this.metadata.get(['entityDefs', entityType, 'collection', 'orderBy']);
                const order = this.metadata.get(['entityDefs', entityType, 'collection', 'order']);
                const className = this.metadata.get(['clientDefs', entityType, 'collection']) || 'collection';
                const defs = this.metadata.get(['entityDefs', entityType]) || {};

                Espo.loader.require(className, (collectionClass: typeof Collection) => {
                    const collection = new collectionClass(null, {
                        entityType: entityType,
                        orderBy: orderBy,
                        order: order,
                        defs: defs,
                        model: Model,
                    });

                    collection.entityType = entityType;
                    collection.maxMaxSize = this.recordListMaxSizeLimit;

                    if (callback) {
                        callback.call(context, collection);
                    }

                    resolve(collection as Collection<T>);
                });
            });
        });
    }
}
