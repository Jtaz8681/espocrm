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

export default class extends EnumFieldView {

    setup() {
        super.setup();

        if (!this.model.isNew()) {
            this.wait(this.setReadOnly(true));
        }
    }

    setupOptions() {
        /** @type {Record<string, Record>} */
        const links = this.getMetadata().get(['entityDefs', this.options.scope, 'links']) || {};

        this.params.options = Object.keys(Espo.Utils.clone(links)).filter(item => {
            if (links[item].type !== 'belongsTo' && links[item].type !== 'hasOne') {
                return;
            }

            if (links[item].noJoin) {
                return;
            }

            if (links[item].disabled) {
                return;
            }

            if (links[item].utility) {
                return;
            }

            return true;
        });

        const scope = this.options.scope;

        this.translatedOptions = {};

        this.params.options.forEach((item) => {
            this.translatedOptions[item] = this.translate(item, 'links', scope);
        });

        this.params.options = this.params.options.sort((v1, v2) => {
            return this.translate(v1, 'links', scope).localeCompare(this.translate(v2, 'links', scope));
        });

        this.params.options.unshift('');
    }
}
