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

export default class extends ModalView {

    className = 'dialog dialog-record'

    templateContent = `<div class="record">{{{record}}}</div>`

    setup() {
        this.buttonList = [
            {
                name: 'deploy',
                text: this.translate('Deploy for Team', 'labels', 'DashboardTemplate'),
                style: 'danger',
                onClick: () => this.actionDeploy(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.headerText = this.model.get('name');

        this.formModel = new Model();
        this.formModel.name = 'None';

        this.formModel.setDefs({
            fields: {
                'team': {
                    type: 'link',
                    entity: 'Team',
                    required: true
                },
                'append': {
                    type: 'bool'
                },
            }
        });

        this.createView('record', 'views/record/edit-for-modal', {
            scope: 'None',
            model: this.formModel,
            selector: '.record',
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                name: 'team',
                                labelText: this.translate('team', 'links'),
                            },
                            {
                                name: 'append',
                                labelText: this.translate('append', 'fields', 'DashboardTemplate'),
                            },
                        ]
                    ]
                }
            ],
        });
    }

    /**
     * @private
     * @return {import('views/record/edit').default}
     */
    getRecordView() {
        return this.getView('record');
    }

    /**
     * @private
     */
    actionDeploy() {
        if (this.getRecordView().processFetch()) {
            Espo.Ajax
                .postRequest('DashboardTemplate/action/deployToTeam', {
                    id: this.model.id,
                    teamId: this.formModel.get('teamId'),
                    append: this.formModel.get('append'),
                })
                .then(() => {
                    Espo.Ui.success(this.translate('Done'));
                    this.close();
                });
        }
    }
}
