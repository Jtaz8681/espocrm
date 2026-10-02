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

/** @module collections/note */

import Collection from 'collection';

class NoteCollection extends Collection {

    /**
     * @type {boolean}
     */
    paginationByNumber = false

    /**
     * @private
     * @type {string|null}
     */
    reactionsCheckDate = null

    /**
     * @type {Record[]}
     */
    pinnedList

    /**
     * @type {number}
     */
    reactionsCheckMaxSize = 0;

    prepareAttributes(response, params) {
        if (Array.isArray(response)) {
            throw new Error("Bad response.");
        }

        const total = this.total;

        const list = super.prepareAttributes(response, params);

        if (params.data && params.data.after) {
            this.total = total >= 0 && response.total >= 0 ?
                total + response.total : total;
        }

        if (response.pinnedList) {
            this.pinnedList = Espo.Utils.cloneDeep(response.pinnedList);
        }

        this.reactionsCheckDate = response.reactionsCheckDate;

        /** @type {Record[]} */
        const updatedReactions = response.updatedReactions;

        if (updatedReactions) {
            updatedReactions.forEach(item => {
                const model = this.get(item.id);

                if (!model) {
                    return;
                }

                model.set(item);
            });
        }

        return list;
    }

    /**
     * Fetch new records.
     *
     * @param {Object} [options] Options.
     * @returns {Promise}
     */
    fetchNew(options) {
        options = options || {};

        options.data = options.data || {};
        options.fetchNew = true;
        options.noRebuild = true;
        options.lengthBeforeFetch = this.length;

        if (this.length) {
            options.data.after = this.models[0].get('createdAt');
            options.remove = false;
            options.at = 0;
            options.maxSize = null;

            if (this.reactionsCheckMaxSize) {
                options.data.reactionsAfter = this.reactionsCheckDate || options.data.after;

                options.data.reactionsCheckNoteIds = this.models
                    .filter(model => model.attributes.type === 'Post')
                    .map(model => model.id)
                    .slice(0, this.reactionsCheckMaxSize)
                    .join(',');
            }
        }

        return this.fetch(options);
    }

    fetch(options) {
        options = {...options};

        if (this.paginationByNumber && options.more) {
            options.more = false;
            options.data = options.data || {};

            const lastModel = this.models.at(this.length - 1);

            if (lastModel) {
                options.data.beforeNumber = lastModel.get('number');
            }
        }

        return super.fetch(options);
    }
}

export default NoteCollection;
