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

/** @module views/import/index */

import View from 'view';

class IndexImportView extends View {

    template = 'import/index'

    formData = null
    fileContents = null

    data() {
        return {
            fromAdmin: this.options.fromAdmin,
        };
    }

    setup() {
        this.entityType = this.options.entityType || null;

        this.startFromStep = 1;

        if (this.options.formData || this.options.fileContents) {
            this.formData = this.options.formData || {};
            this.fileContents = this.options.fileContents || null;

            this.entityType = this.formData.entityType || null;

            if (this.options.step) {
                this.startFromStep = this.options.step;
            }
        }
    }

    changeStep(num, result) {
        this.step = num;

        if (num > 1) {
            this.setConfirmLeaveOut(true);
        }

        this.createView('step', 'views/import/step' + num.toString(), {
            selector: '> .import-container',
            entityType: this.entityType,
            formData: this.formData,
            result: result,
        }, view => {
            view.render();
        });

        let url = '#Import';

        if (this.options.fromAdmin && this.step === 1) {
            url = '#Admin/import';
        }

        if (this.step > 1) {
            url += '/index/step=' + this.step;
        }

        this.getRouter().navigate(url, {trigger: false});
    }

    afterRender() {
        this.changeStep(this.startFromStep);
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Import', 'labels', 'Admin'));
    }

    setConfirmLeaveOut(value) {
        this.getRouter().confirmLeaveOut = value;
    }
}

export default IndexImportView;
