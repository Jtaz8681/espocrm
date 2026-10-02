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

import ModalView, {ModalViewOptions} from 'views/modal';
import Ajax from 'ajax';
import EditForModalRecordView from 'views/record/edit-for-modal';
import Model from 'model';
import DateTime from 'date-time';
import {inject} from 'di';
import DateFieldView from 'views/fields/date';
import Ui from 'ui';

export default class ResetFetchDataModalView extends ModalView<{
    model: Model,
    options: ModalViewOptions,
}> {

    templateContent = `
        <div
            class="alert alert-warning"
        >{{translate 'resetFetchData' category='messages' scope='EmailAccount'}}</div>
        <div class="record-container no-side-margin">{{{record}}}</div>
    `
    private recordView: EditForModalRecordView;

    private formModel: Model<{
        fetchSince: string | null;
    }>;

    @inject(DateTime)
    private dateTime: DateTime

    protected setup() {
        this.buttonList = [
            {
                name: 'reset',
                label: 'Reset',
                style: 'danger',
                onClick: () => this.processReset(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
                onClick: () => this.close(),
            },
        ];

        this.formModel = new Model({
            fetchSince: this.dateTime.getToday(),
        });

        this.recordView = new EditForModalRecordView({
            model: this.formModel,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new DateFieldView({
                                    name: 'fetchSince',
                                    labelText: this.translate('fetchSince', 'fields', 'EmailAccount'),
                                    params: {
                                        required: true,
                                    },
                                })
                            },
                            false,
                        ]
                    ]
                }
            ],
        });

        this.assignView('record', this.recordView);
    }

    private async processReset() {
        if (this.recordView.validate()) {
            return;
        }

        await this.confirm({message: this.translate('confirmation', 'messages')});

        Ui.notifyWait();
        this.disableButton('reset');

        let response: Record<string, any>;

        try {
            response = await Ajax.postRequest(`${this.model.entityType!}/${this.model.id!}/resetFetchData`, {
                fetchSince: this.formModel.attributes.fetchSince,
            }) as Record<string, any>;
        } catch (e) {
            this.enableButton('reset');

            return;
        }

        this.model.setMultiple(response, {sync: true});

        Ui.success(this.translate('Done'));

        this.close();
    }
}
