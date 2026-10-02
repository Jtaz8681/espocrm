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

/** @module model-factory */

import Metadata from 'metadata';
import Model from 'model';

/**
 * A model factory.
 */
export default class ModelFactory {

    constructor(private metadata: Metadata) {}

    /**
     * Create a model.
     *
     * @param entityType An entity type.
     * @param [callback] Deprecated.
     * @param [context] Deprecated.
     * @returns A created model.
     */
    create(entityType: string, callback?: Function, context?: object): Promise<Model> {
        return new Promise(resolve => {
            context = context || this;

            this.getSeed(entityType, Seed => {
                const model = new Seed({}, {
                    entityType: entityType,
                    defs: this.metadata.get(['entityDefs', entityType]) || {},
                });

                if (callback) {
                    callback.call(context, model);
                }

                resolve(model);
            });
        });
    }

    /**
     * Get a class.
     *
     * @param {string} entityType An entity type.
     * @param callback A callback.
     * @public
     * @internal
     */
    getSeed(entityType: string, callback: (modelClass: typeof Model) => void) {
        const className = (this.metadata.get(['clientDefs', entityType, 'model']) ?? 'model') as string;

        Espo.loader.require(className, (modelClass: typeof Model) => callback(modelClass));
    }
}
