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
import EditForModalRecordView from 'views/record/edit-for-modal';
import Model from 'model';
import FileFieldView from 'views/fields/file';

class ImportEmlModal extends ModalView {

    // language=Handlebars
    templateContent = `
        <div class="record no-side-margin">{{{record}}}</div>
    `

    setup() {
        this.headerText = this.translate('Import EML', 'labels', 'Email');

        this.addButton({
            name: 'import',
            label: 'Proceed',
            style: 'danger',
            onClick: () => this.actionImport(),
        });

        this.addButton({
            name: 'cancel',
            label: 'Cancel',
            onClick: () => this.close(),
        });

        this.model = new Model({}, {entityType: 'ImportEml'});

        this.recordView = new EditForModalRecordView({
            model: this.model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new FileFieldView({
                                    name: 'file',
                                    params: {
                                        required: true,
                                        accept: ['.eml'],
                                    },
                                    labelText: this.translate('file', 'otherFields', 'Email'),
                                })
                            }
                        ]
                    ]
                }
            ]
        });

        this.assignView('record', this.recordView, '.record');
    }

    actionImport() {
        if (this.recordView.validate()) {
            return;
        }

        this.disableButton('import');
        Espo.Ui.notifyWait();

        Espo.Ajax
            .postRequest('Email/importEml', {fileId: this.model.attributes.fileId})
            .then(/** {id: string} */response => {
                Espo.Ui.notify(false);

                this.getRouter().navigate(`Email/view/${response.id}`, {trigger: true});
            })
            .catch(() => this.enableButton('import'));
    }
}

export default ImportEmlModal;
