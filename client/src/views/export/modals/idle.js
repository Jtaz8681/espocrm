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

class ExportIdleModalView extends ModalView {

    template = 'export/modals/idle'

    className = 'dialog dialog-record'
    checkInterval = 4000

    data() {
        return {
            infoText: this.translate('infoText', 'messages', 'Export'),
        };
    }

    setup() {
        this.addActionHandler('download', () => this.actionDownload());

        this.action = this.options.action;
        this.id = this.options.id;
        this.status = 'Pending';

        this.headerText = this.translate('Export');

        this.model = new Model();
        this.model.name = 'Export';

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
                'attachmentId': {
                    type: 'varchar',
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
                                labelText: this.translate('status', 'fields', 'Export'),
                            }
                        ]
                    ]
                }
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

            Espo.Ajax.postRequest(`Export/${this.id}/subscribe`);
        });

        this.checkStatus();
    }

    /**
     * @private
     */
    checkStatus() {
        Espo.Ajax
            .getRequest(`Export/${this.id}/status`)
            .then(response => {
                const status = response.status;

                this.model.set('status', status);

                if (status === 'Pending' || status === 'Running') {
                    setTimeout(() => this.checkStatus(), this.checkInterval);

                    return;
                }

                this.model.set({
                    attachmentId: response.attachmentId,
                });

                if (status === 'Success') {
                    this.trigger('success', {
                        attachmentId: response.attachmentId,
                    });

                    this.showDownload();
                }

                if (this.$el) {
                    this.$el.find('.info-text').addClass('hidden');
                }
            });
    }

    /**
     * @private
     */
    showDownload() {
        this.$el.find('.download-container').removeClass('hidden');

        const $download = this.$el.find('[data-action="download"]');

        $download.removeClass('hidden');
    }

    /**
     * @private
     */
    actionDownload() {
        this.trigger('download', this.model.get('attachmentId'));

        this.close();
    }
}

export default ExportIdleModalView;
