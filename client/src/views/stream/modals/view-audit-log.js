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
import ListStreamRecordView from 'views/stream/record/list';
import $ from 'jquery';

class StreamViewAuditLogModalView extends ModalView {

    templateContent = '<div class="record list-container">{{{record}}}</div>'

    backdrop = true

    /**
     * @param {{model: import('model').default}} options
     */
    constructor(options) {
        super(options);
    }

    setup() {
        const name = this.model.get('name') || this.model.id;

        this.$header = $('<span>')
            .append(
                $('<span>').text(name),
                ' <span class="chevron-right"></span> ',
                $('<span>').text(this.translate('Audit Log'))
            );

        this.buttonList = [
            {
                name: 'close',
                label: 'Close',
                onClick: dialog => {
                    dialog.close();
                },
            }
        ];

        this.wait(
            this.getCollectionFactory().create('Note').then(collection => {
                collection.url = `${this.model.entityType}/${this.model.id}/updateStream`;
                collection.maxSize = this.getConfig().get('recordsPerPage');

                const listView = new ListStreamRecordView({
                    collection: collection,
                    model: this.model,
                    // Prevents 'No Data' being displayed.
                    skipBuildRows: true,
                    type: 'listAuditLog',
                });

                Espo.Ui.notifyWait();

                return this.assignView('record', listView, '.record')
                    .then(() => {
                        collection.fetch()
                            .then(() => Espo.Ui.notify(false));
                    });
            })
        );
    }
}

export default StreamViewAuditLogModalView;
