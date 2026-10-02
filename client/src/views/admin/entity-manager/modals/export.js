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
import EditForModalRecordView from 'views/record/edit-for-modal';
import VarcharFieldView from 'views/fields/varchar';

class EntityManagerExportModalView extends ModalView {

    // language=Handlebars
    templateContent = `
        <div class="record-container no-side-margin">{{{record}}}</div>
    `

    setup() {
        this.headerText = this.translate('Export');

        this.buttonList = [
            {
                name: 'export',
                label: 'Export',
                style: 'danger',
                onClick: () => this.export(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        /** @type {Record} */
        const manifest = this.getConfig().get('customExportManifest') || {};

        this.model = new Model({
            name: manifest.name ?? null,
            module: manifest.module ?? null,
            version: manifest.version ?? '0.0.1',
            author: manifest.author ?? null,
            description: manifest.description ?? null,
        });

        this.recordView = new EditForModalRecordView({
            model: this.model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new VarcharFieldView({
                                    name: 'name',
                                    labelText: this.translate('name', 'fields'),
                                    params: {
                                        pattern: '$latinLettersDigitsWhitespace',
                                        required: true,
                                    },

                                }),
                            },
                            {
                                view: new VarcharFieldView({
                                    name: 'module',
                                    labelText: this.translate('module', 'fields', 'EntityManager'),
                                    params: {
                                        pattern: '[A-Z][a-z][A-Za-z]+',
                                        required: true,
                                    },
                                }),
                            },
                        ],
                        [
                            {
                                view: new VarcharFieldView({
                                    name: 'version',
                                    labelText: this.translate('version', 'fields', 'EntityManager'),
                                    params: {
                                        pattern: '[0-9]+\\.[0-9]+\\.[0-9]+',
                                        required: true,
                                    },
                                }),
                            },
                            false
                        ],
                        [
                            {
                                view: new VarcharFieldView({
                                    name: 'author',
                                    labelText: this.translate('author', 'fields', 'EntityManager'),
                                    params: {
                                        required: true,
                                    },
                                }),

                            },
                            {
                                view: new VarcharFieldView({
                                    name: 'description',
                                    labelText: this.translate('description', 'fields'),
                                    params: {},

                                }),
                            },
                        ],
                    ]
                }
            ]
        });

        this.assignView('record', this.recordView);
    }

    export() {
        const data = this.recordView.fetch();

        if (this.recordView.validate()) {
            return;
        }

        this.disableButton('export');

        Espo.Ui.notifyWait();

        Espo.Ajax
            .postRequest('EntityManager/action/exportCustom', data)
            .then(response => {
                this.close();

                this.getConfig().set('customExportManifest', data);

                Espo.Ui.success(this.translate('Done'));

                window.location = this.getBasePath() + '?entryPoint=download&id=' + response.id;
            })
            .catch(() => this.enableButton('export'));
    }
}

export default EntityManagerExportModalView;
