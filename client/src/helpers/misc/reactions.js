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

import {inject, register} from 'di';
import Settings from 'models/settings';
import Metadata from 'metadata';

@register()
class ReactionsHelper {

    /**
     * @type Settings
     */
    @inject(Settings)
    config

    /**
     * @type Metadata
     */
    @inject(Metadata)
    metadata

    /**
     * @private
     * @type {{
     *     type: string,
     *     iconClass: string,
     * }[]}
     */
    list

    /**
     * @return {{
     *     type: string,
     *     iconClass: string,
     * }[]}
     */
    getDefinitionList() {
        if (!this.list) {
            this.list = this.metadata.get('app.reactions.list') || [];
        }

        return this.list;
    }

    /**
     * @return {string[]}
     */
    getAvailableReactions() {
        return this.config.get('availableReactions') || []
    }

    /**
     * @param {string|null} type
     * @return {string|null}
     */
    getIconClass(type) {
        const item = this.getDefinitionList().find(it => it.type === type);

        if (!item) {
            return null;
        }

        return item.iconClass;
    }
}

export default ReactionsHelper;
