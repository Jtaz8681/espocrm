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

import EditRecordView from 'views/record/edit';

export default class extends EditRecordView {

    saveAndContinueEditingAction = true

    setup() {
        super.setup();

        if (!this.model.isNew()) {
            this.setFieldReadOnly('entityType');
        }

        if (this.model.get('entityType')) {
            this.showField('variables');
        } else {
            this.hideField('variables');
        }

        if (this.model.isNew()) {
            const storedData = {};

            this.listenTo(this.model, 'change:entityType', () => {
                const entityType = this.model.get('entityType');

                if (!entityType) {
                    this.model.set('header', null);
                    this.model.set('body', null);
                    this.model.set('footer', null);

                    this.hideField('variables');

                    return;
                }

                this.showField('variables');

                if (entityType in storedData) {
                    this.model.set('header', storedData[entityType].header);
                    this.model.set('body', storedData[entityType].body);
                    this.model.set('footer', storedData[entityType].footer);
                    this.model.set('style', storedData[entityType].style);

                    return;
                }

                let header, body, footer;

                let sourceType = null;
                let style = null;

                if (
                    this.getMetadata().get(['entityDefs', 'Template', 'defaultTemplates', entityType])
                ) {
                    sourceType = entityType;
                } else {
                    const scopeType = this.getMetadata().get(['scopes', entityType, 'type']);

                    if (
                        scopeType &&
                        this.getMetadata().get(['entityDefs', 'Template', 'defaultTemplates', scopeType])
                    ) {
                        sourceType = scopeType;
                    }
                }

                if (sourceType) {
                    header = this.getMetadata().get(
                        ['entityDefs', 'Template', 'defaultTemplates', sourceType, 'header']
                    );

                    body = this.getMetadata().get(
                        ['entityDefs', 'Template', 'defaultTemplates', sourceType, 'body']
                    );

                    footer = this.getMetadata().get(
                        ['entityDefs', 'Template', 'defaultTemplates', sourceType, 'footer']
                    );

                    style = this.getMetadata().get(['entityDefs', 'Template', 'defaultTemplates', sourceType, 'style']);
                }

                body = body || null;
                header = header || null;
                footer = footer || null;

                this.model.set('body', body);
                this.model.set('header', header);
                this.model.set('footer', footer);
                this.model.set('style', style);
            });

            this.listenTo(this.model, 'change', (e, o) => {
                if (!o.ui) {
                    return;
                }

                if (
                    !this.model.hasChanged('header') &&
                    !this.model.hasChanged('body') &&
                    !this.model.hasChanged('footer') &&
                    !this.model.hasChanged('style')
                ) {
                    return;
                }

                const entityType = this.model.get('entityType');

                if (!entityType) {
                    return;
                }

                storedData[entityType] = {
                    header: this.model.get('header'),
                    body: this.model.get('body'),
                    footer: this.model.get('footer'),
                    style: this.model.get('style'),
                };
            });
        }
    }
}
