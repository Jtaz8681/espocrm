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
import DatetimeFieldView from 'views/fields/datetime';
import moment from 'moment';

// noinspection JSUnusedGlobalSymbols
export default class EmailScheduleSendModalView extends ModalView {

    // language=Handlebars
    templateContent = `<div class="record no-side-margin">{{{record}}}</div>`

    /**
     * @type {Model}
     */
    formModel

    /**
     * @type {EditForModalRecordView}
     */
    recordView

    /**
     * @param {{
     *     model: import('model').default,
     *     onSave: function(): void,
     * }} options
     */
    constructor(options) {
        super(options);

        this.onSave = options.onSave;
    }

    setup() {
        this.headerText = this.translate('Schedule Send', 'labels', 'Email');

        this.buttonList.push({
            name: 'schedule',
            label: 'Schedule',
            style: 'danger',
            onClick: () => this.actionSchedule(),
        });

        this.buttonList.push({
            name: 'cancel',
            label: 'Cancel',
            onClick: () => this.close(),
        });

        this.formModel = new Model(
            {
                now: this.getDateTime().getNow(),
                sendAt: this.getSendAt(),
            }
        );

        this.recordView = new EditForModalRecordView({
            model: this.formModel,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new DatetimeFieldView({
                                    name: 'sendAt',
                                    labelText: this.translate('sendAt', 'fields', 'Email'),
                                    params: {
                                        required: true,
                                        after: 'now',
                                    },
                                    otherFieldLabelText: this.translate('Now'),
                                })
                            },
                            false
                        ]
                    ]
                }
            ],
        });

        this.assignView('record', this.recordView, '.record');
    }

    /**
     * @private
     * @return {string}
     */
    getSendAt() {
        const sendAtMoment = moment.utc(this.getDateTime().getNow(10));

        if (sendAtMoment.isBefore(moment().add(1, 'minutes'))) {
            sendAtMoment.add(10, 'minutes');
        }

        return sendAtMoment.format(this.getDateTime().internalDateTimeFormat);
    }

    async actionSchedule() {
        if (this.recordView.validate()) {
            return;
        }

        this.disableButton('schedule');
        Espo.Ui.notifyWait();

        this.model.set({
            status: 'Draft',
            sendAt: this.formModel.attributes.sendAt,
        });

        try {
            await this.model.save();
        } catch (e) {
            this.enableButton('schedule');

            return;
        }

        const name = this.model.attributes.subject;
        const url = `#Email/view/${this.model.id}`;

        const message = this.translate('Scheduled') + '\n' + `[${name}](${url})`;

        Espo.Ui.notify(message, 'success', 4000);

        this.onSave();
    }
}
