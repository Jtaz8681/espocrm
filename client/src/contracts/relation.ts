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

import type Model from 'model';
import type {AdvancedFilter} from 'search-manager';

export interface SelectRelatedFilters {
    /**
     * Advanced filters. A field name as a key.
     */
    advanced?: Record<string, AdvancedFilter>;
    /**
     * Bool filters.
     */
    bool?: string[];
    /**
     * A primary filter.
     */
    primary?: string;
    /**
     * A field to order by.
     */
    orderBy?: string;
    /**
     * An order direction.
     */
    order?: 'asc' | 'desc';
}

/**
 * Select related records. Prepares filters for selecting records to relate.
 */
export interface SelectRelatedHandler<M extends Model = Model> {

    /**
     * Filters.
     *
     * @param model A parent model.
     * @return {Promise<SelectRelatedFilters>} Filters.
     */
    getFilters(model: M): Promise<SelectRelatedFilters>;
}

/**
 * Prepares attributes for a related record that is being created.
 *
 * @param model A parent model.
 */
export interface CreateRelatedHandler<M extends Model = Model> {

    /**
     * Get attributes for a new record.
     *
     * @param model A model.
     * @param link A link name.
     */
    getAttributes(model: M, link: string): Promise<Record<string, unknown>>;
}

/**
 * Prepares attributes to set to the model when selecting and clearing a related record.
 *.
 * @template M A related model type.
 * @template P A parent model type.
 */
export interface SelectFieldHandler<M extends Model = Model> {

    /**
     * Get attributes to set to the model after selecting the related model.
     *
     * @param model A related model.
     * @return {Promise<Record<string, unknown>>} Attributes.
     */
    getAttributes: (model: M) => Promise<Record<string, unknown>>;

    /**
     * Get attributes to set to the model after clearing a related model. Usually, it's null values.
     *
     * @return {Promise<Record<string, unknown>>} Attributes.
     */
    getClearAttributes: () => Promise<Record<string, unknown>>;
}
