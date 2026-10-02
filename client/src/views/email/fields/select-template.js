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

import LinkFieldView from 'views/fields/link';

export default class extends LinkFieldView {

    editTemplate = 'email/fields/select-template/edit'

    foreignScope = 'EmailTemplate'

    setup() {
        super.setup();

        this.on('change', () => {
            const id = this.model.get(this.idName);

            if (id) {
                this.loadTemplate(id);
            }
        });
    }

    getSelectPrimaryFilterName() {
        return 'actual';
    }

    loadTemplate(id) {
        let to = this.model.get('to') || '';
        let emailAddress = null;

        to = to.trim();

        if (to) {
            emailAddress = to.split(';')[0].trim();
        }

        Espo.Ajax
            .postRequest(`EmailTemplate/${id}/prepare`, {
                emailAddress: emailAddress,
                parentType: this.model.get('parentType'),
                parentId: this.model.get('parentId'),
                relatedType: this.model.get('relatedType'),
                relatedId: this.model.get('relatedId'),
            })
            .then(data => {
                this.model.trigger('insert-template', data);

                this.emptyField();
            })
            .catch(() => {
                this.emptyField();
            });
    }

    /**
     * @private
     */
    emptyField() {
        this.model.set(this.idName, null);
        this.model.set(this.nameName, null);
    }
}
