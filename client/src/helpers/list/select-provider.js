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
import LayoutManager from 'layout-manager';
import Metadata from 'metadata';
import FieldManager from 'field-manager';

class SelectProvider {


    /**
     * @type {LayoutManager}
     * @private
     */
    @inject(LayoutManager)
    layoutManager

    /**
     * @type {Metadata}
     * @private
     */
    @inject(Metadata)
    metadata

    /**
     * @type {FieldManager}
     * @private
     */
    @inject(FieldManager)
    fieldManager

    /**
     * Get select attributes.
     *
     * @param {string} entityType
     * @param {string} [layoutName='list']
     * @return {Promise<string[]>}
     */
    get(entityType, layoutName) {
        return new Promise(resolve => {
            this.layoutManager.get(entityType, layoutName || 'list', layout => {
                const list = this.getFromLayout(entityType, layout);

                resolve(list);
            });
        });
    }

    /**
     * Get select attributes from a layout.
     *
     * @param {string} entityType
     * @param {module:views/record/list~columnDefs[]} listLayout
     * @param {import('helpers/list/settings').default} [settings]
     * @return {string[]}
     */
    getFromLayout(entityType, listLayout, settings) {
        const list = [];

        listLayout.forEach(item => {
            if (!item.name) {
                return;
            }

            if (settings?.isColumnHidden(item.name, item.hidden)) {
                return;
            }

            if (!settings && item.hidden) {
                return;
            }

            const field = item.name;
            const type = this.metadata.get(`entityDefs.${entityType}.fields.${field}.type`);

            if (!type) {
                return;
            }

            list.push(...this.fieldManager.getEntityTypeFieldAttributeList(entityType, field));
        });

        return list;
    }
}

export default SelectProvider;
