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

import ModalView from 'views/modal';
import Model from 'model';

class MassActionModalView extends ModalView {

    template = 'modals/mass-action'

    className = 'dialog dialog-record'
    checkInterval = 4000

    data() {
        return {
            infoText: this.translate('infoText', 'messages', 'MassAction'),
        };
    }

    setup() {
        this.action = this.options.action;
        this.id = this.options.id;
        this.status = 'Pending';

        this.headerText =
            this.translate('Mass Action', 'scopeNames') + ' · ' +
            this.translate(this.action, 'massActions', this.options.scope);

        this.model = new Model();
        this.model.name = 'MassAction';

        this.model.setDefs({
            fields: {
                'status': {
                    type: 'enum',
                    readOnly: true,
                    options: [
                        'Pending',
                        'Running',
                        'Success',
                        'Failed',
                    ],
                    style: {
                        'Success': 'success',
                        'Failed': 'danger',
                    },
                },
                'processedCount': {
                    type: 'int',
                    readOnly: true,
                },
            }
        });

        this.model.set({
            status: this.status,
            processedCount: null,
        });

        this.createView('record', 'views/record/edit-for-modal', {
            scope: 'None',
            model: this.model,
            selector: '.record',
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                name: 'status',
                                labelText: this.translate('status', 'fields', 'MassAction'),
                            },
                            {
                                name: 'processedCount',
                                labelText: this.translate('processedCount', 'fields', 'MassAction'),
                            },
                        ],
                    ],
                },
            ],
        });

        this.on('close', () => {
            const status = this.model.get('status');

            if (
                status !== 'Pending' &&
                status !== 'Running'
            ) {
                return;
            }

            Espo.Ajax.postRequest(`MassAction/${this.id}/subscribe`);
        });

        this.checkStatus();
    }

    checkStatus() {
        Espo.Ajax
            .getRequest(`MassAction/${this.id}/status`)
            .then(response => {
                const status = response.status;

                this.model.set('status', status);

                if (status === 'Pending' || status === 'Running') {
                    setTimeout(() => this.checkStatus(), this.checkInterval);

                    return;
                }

                this.model.set({
                    processedCount: response.processedCount,
                });

                if (status === 'Success') {
                    this.trigger('success', {
                        count: response.processedCount,
                    });
                }

                if (this.$el) {
                    this.$el.find('.info-text').addClass('hidden');
                }
            });
    }
}

export default MassActionModalView;
