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

/**
 * @since 9.1.1
 * @internal For future use.
 */
export default class {

    /**
     * @type {Metadata}
     * @private
     */
    @inject(Metadata)
    metadata

    /**
     * Get foreign field params.
     *
     * @param {string} entityType
     * @param {string} field
     * @return {Record|null}
     */
    get(entityType, field) {
        /** @type {Record|null} */
        const params = this.metadata.get(`entityDefs.${entityType}.fields.${field}`);

        if (!params) {
            return null;
        }

        const foreignField = params.field;
        const link = params.link;

        const foreignEntityType = this.metadata.get(`entityDefs.${entityType}.links.${link}.entity`);

        if (!foreignEntityType) {
            return null;
        }

        return this.metadata.get(`entityDefs.${foreignEntityType}.links.${foreignField}`);
    }
}
