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

import DetailRecordView from 'views/record/detail';

class ImportDetailRecordView extends DetailRecordView {

    readOnly = true
    returnUrl = '#Import/list'
    checkInterval = 5
    resultPanelFetchLimit = 10
    duplicateAction = false

    setup() {
        super.setup();

        this.fetchCounter = 0;
        this.setupChecking();

        this.hideActionItem('delete');
    }

    setupChecking() {
        if (!this.model.has('status')) {
            this.listenToOnce(this.model, 'sync', this.setupChecking.bind(this));

            return;
        }

        if (!~['In Process', 'Pending', 'Standby'].indexOf(this.model.get('status'))) {
            return;
        }

        setTimeout(this.runChecking.bind(this), this.checkInterval * 1000);

        this.on('remove', () => {
            this.stopChecking = true;
        });
    }

    runChecking() {
        if (this.stopChecking) {
            return;
        }

        this.model.fetch().then(() => {
            const isFinished = !~['In Process', 'Pending', 'Standby'].indexOf(this.model.get('status'));

            if (this.fetchCounter < this.resultPanelFetchLimit && !isFinished) {
                this.fetchResultPanels();
            }

            if (isFinished) {
                this.fetchResultPanels();

                return;
            }

            setTimeout(this.runChecking.bind(this), this.checkInterval * 1000);
        });

        this.fetchCounter++;
    }

    fetchResultPanels() {
        const bottomView = this.getView('bottom');

        if (!bottomView) {
            return;
        }

        const importedView = bottomView.getView('imported');

        if (importedView && importedView.collection) {
            importedView.collection.fetch();
        }

        const duplicatesView = bottomView.getView('duplicates');

        if (duplicatesView && duplicatesView.collection) {
            duplicatesView.collection.fetch();
        }

        const updatedView = bottomView.getView('updated');

        if (updatedView && updatedView.collection) {
            updatedView.collection.fetch();
        }
    }
}

export default ImportDetailRecordView;
