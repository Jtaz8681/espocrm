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

import MultiEnumFieldView from 'views/fields/multi-enum';
import MultiSelect from 'ui/multi-select';

export default class FormulaAttributeFieldView extends MultiEnumFieldView {

    setupOptions() {
        super.setupOptions();

        if (this.options.attributeList) {
            this.params.options = this.options.attributeList;

            return;
        }

        const attributeList = this.getFieldManager()
            .getEntityTypeAttributeList(this.options.scope)
            .concat(['id'])
            .sort();

        const links = this.getMetadata().get(['entityDefs', this.options.scope, 'links']) || {};

        const linkList = [];

        Object.keys(links).forEach(link => {
            const type = links[link].type;
            const scope = links[link].entity;

            if (!type) {
                return;
            }

            if (!scope) {
                return;
            }

            if (
                links[link].disabled ||
                links[link].utility
            ) {
                return;
            }

            if (~['belongsToParent', 'hasOne', 'belongsTo'].indexOf(type)) {
                linkList.push(link);
            }
        });

        linkList.sort();

        linkList.forEach(link => {
            const scope = links[link].entity;

            const linkAttributeList = this.getFieldManager().getEntityTypeAttributeList(scope)
                .sort();

            linkAttributeList.forEach(item => {
                attributeList.push(link + '.' + item);
            });
        });

        this.params.options = attributeList;
    }

    afterRender() {
        super.afterRender();

        if (this.$element) {
            MultiSelect.focus(this.$element);
        }
    }
}
