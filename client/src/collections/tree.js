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

/** @module collections/tree */

import Collection from 'collection';

class TreeCollection extends Collection {

    /**
     * @type {string | null}
     */
    parentId

    /**
     * @type {string|null}
     */
    currentId = null

    /**
     * @type {string[]|null}
     */
    path

    /**
     * @type {string[]|null}
     */
    openPath

    /**
     * @internal
     * @type {string | null}
     */
    currentCategoryId

    /**
     * @internal
     * @type {string | null}
     */
    currentCategoryName

    /**
     * @return {TreeCollection}
     */
    createSeed() {
        const seed = new this.constructor();

        seed.url = this.url;
        seed.model = this.model;
        seed.name = this.name;
        seed.entityType = this.entityType;
        seed.defs = this.defs;

        return seed;
    }

    prepareAttributes(response, options) {
        if (Array.isArray(response)) {
            throw new Error("Bad response.");
        }

        const list = super.prepareAttributes(response, options);

        const seed = this.clone();

        seed.reset();

        this.path = response.path;
        this.openPath = response.openPath ?? null;

        /**
         * @type {{
         *     id: string,
         *     name: string,
         *     upperId?: string,
         *     upperName?: string,
         * }|null}
         */
        this.categoryData = response.data || null;

        const prepare = (list, depth) => {
            list.forEach(data => {
                data.depth = depth;

                const childCollection = this.createSeed();

                childCollection.parentId = data.id;

                if (data.childList) {
                    if (data.childList.length) {
                        prepare(data.childList, depth + 1);

                        childCollection.set(data.childList);
                        data.childCollection = childCollection;

                        return;
                    }

                    data.childCollection = childCollection;

                    return;
                }

                if (data.childList === null) {
                    data.childCollection = null;

                    return;
                }

                data.childCollection = childCollection;
            });
        };

        prepare(list, 0);

        return list;
    }

    fetch(options) {
        options = options || {};
        options.data = options.data || {};

        if (this.parentId) {
            options.data.parentId = this.parentId;
        }

        if (this.currentId) {
            options.data.currentId = this.currentId;
        }

        return super.fetch(options);
    }

    clone(options = {}) {
        options = {...options};

        // Prevents recurring clone.
        options.withModels = false;

        return super.clone(options);
    }
}

export default TreeCollection;
