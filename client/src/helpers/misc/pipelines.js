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
import AppParams from 'app-params';
import Metadata from 'metadata';

/**
 * @since 10.0.0
 */
export default class PipelinesHelper {

    /**
     * @private
     * @type {AppParams}
     */
    @inject(AppParams)
    appParams

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @param {string} entityType
     * @return {{
     *     id: string,
     *     name: string,
     *     stages: {
     *         id: string,
     *         name: string,
     *         style: string|null,
     *     }[],
     *     color: number|null,
     * }[]}
     */
    get(entityType) {
        return (this.appParams.get('pipelines') ?? {})[entityType] ?? [];
    }

    /**
     * @param {string} entityType
     * @return {boolean}
     */
    isEnabled(entityType) {
        return this.metadata.get(`scopes.${entityType}.pipelines`) === true;
    }
}
