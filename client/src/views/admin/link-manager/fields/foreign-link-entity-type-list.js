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

import ChecklistFieldView from 'views/fields/checklist';

export default class extends ChecklistFieldView {

    setup() {
        this.params.translation = 'Global.scopeNames';

        super.setup();
    }

    afterRender () {
        super.afterRender();

        this.controlOptionsAvailability();
    }

    controlOptionsAvailability() {
        this.params.options.forEach(item => {
            const link = this.model.get('link');
            const linkForeign = this.model.get('linkForeign');
            const entityType = this.model.get('entity');

            const linkDefs = this.getMetadata().get(['entityDefs', item, 'links']) || {};

            let isFound = false;

            for (const i in linkDefs) {
                if (
                    linkDefs[i].foreign === link &&
                    !linkDefs[i].isCustom &&
                    linkDefs[i].entity === entityType
                ) {
                    isFound = true;
                } else if (i === linkForeign && linkDefs[i].type !== 'hasChildren') {
                    isFound = true;
                }
            }

            if (isFound) {
                this.$el.find(`input[data-name="checklistItem-foreignLinkEntityTypeList-${item}"]`)
                    .attr('disabled', 'disabled');
            }
        });
    }
}
