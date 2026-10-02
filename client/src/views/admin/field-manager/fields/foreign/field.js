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

        this.listenTo(this.model, 'change:field', () => {
            this.manageField();
        });

        this.viewValue = this.model.get('view');
    }

    setupOptions() {
        this.listenTo(this.model, 'change:link', () => {
            this.setupOptionsByLink();
            this.reRender();
        });

        this.setupOptionsByLink();
    }

    setupOptionsByLink() {
        this.typeList = this.getMetadata().get(['fields', 'foreign', 'fieldTypeList']);

        const link = this.model.get('link');

        if (!link) {
            this.params.options = [''];

            return;
        }

        const scope = this.getMetadata().get(['entityDefs', this.options.scope, 'links', link, 'entity']);

        if (!scope) {
            this.params.options = [''];

            return;
        }

        /** @type {Record<string, Record>} */
        const fields = this.getMetadata().get(['entityDefs', scope, 'fields']) || {};

        this.params.options = Object.keys(Espo.Utils.clone(fields)).filter(item => {
            const type = fields[item].type;

            if (!~this.typeList.indexOf(type)) {
                return;
            }

            if (
                fields[item].disabled ||
                fields[item].utility ||
                fields[item].directAccessDisabled ||
                fields[item].notStorable
            ) {
                return;
            }

            return true;
        });

        this.translatedOptions = {};

        this.params.options.forEach(item => {
            this.translatedOptions[item] = this.translate(item, 'fields', scope);
        });

        this.params.options = this.params.options.sort((v1, v2) => {
            return this.translate(v1, 'fields', scope).localeCompare(this.translate(v2, 'fields', scope));
        });

        this.params.options.unshift('');
    }

    manageField() {
        if (!this.model.isNew()) {
            return;
        }

        const link = this.model.get('link');
        const field = this.model.get('field');

        if (!link || !field) {
            return;
        }

        const scope = this.getMetadata().get(['entityDefs', this.options.scope, 'links', link, 'entity']);

        if (!scope) {
            return;
        }

        const type = this.getMetadata().get(['entityDefs', scope, 'fields', field, 'type']);

        this.viewValue = this.getMetadata().get(['fields', 'foreign', 'fieldTypeViewMap', type]);
    }

    fetch() {
        const data = super.fetch();

        if (this.model.isNew()) {
            if (this.viewValue) {
                data['view'] = this.viewValue;
            }
        }

        return data;
    }
}
