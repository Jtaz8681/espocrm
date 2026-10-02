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

import EnumFieldView from 'views/fields/enum';

// noinspection JSUnusedGlobalSymbols
export default class extends EnumFieldView {

    setup() {
        /** @type {string[]} */
        const optionList = this.getMetadata().get('entityDefs.Opportunity.fields.stage.options', []);

        /** @type {Record.<string, number|null>} */
        const probabilityMap = this.getMetadata().get('entityDefs.Opportunity.fields.stage.probabilityMap', {});

        this.params.options = [];

        optionList.forEach(item => {
            if (!probabilityMap[item]) {
                return;
            }

            if (probabilityMap[item] === 100) {
                return;
            }

            this.params.options.push(item);
        });

        this.params.translation = 'Opportunity.options.stage';

        super.setup();
    }
}
