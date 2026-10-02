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

import RecordController from 'controllers/record';

class ImportController extends RecordController {

    defaultAction = 'index'

    storedData

    checkAccessGlobal() {
        if (this.getAcl().checkScope('Import')) {
            return true;
        }

        return false;
    }

    checkAccess(action) {
        if (this.getAcl().checkScope('Import')) {
            return true;
        }

        return false;
    }

    // noinspection JSUnusedGlobalSymbols
    /**
     * @param {{
     *     step?: int|string,
     *     fromAdmin?: boolean,
     *     formData?: Object
     * }} o
     */
    actionIndex(o) {
        o = o || {};

        let step = null;

        if (o.step) {
            step = parseInt(step);
        }

        let formData = null;
        let fileContents = null;

        if (o.formData) {
            this.storedData = undefined;
        }

        if (this.storedData) {
            formData = this.storedData.formData;
            fileContents = this.storedData.fileContents;
        }

        if (!formData) {
            step = null;
        }

        formData = formData || o.formData;

        this.main('views/import/index', {
            step: step,
            formData: formData,
            fileContents: fileContents,
            fromAdmin: o.fromAdmin,
        }, /** module:views/import/index */ view => {
            this.listenTo(view, 'change', () => {
                this.storedData = {
                    formData: view.formData,
                    fileContents: view.fileContents,
                };
            });

            this.listenTo(view, 'done', () => {
                this.storedData = undefined;
            });

            view.render();
        });
    }
}

export default ImportController;
