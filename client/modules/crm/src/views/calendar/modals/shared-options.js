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
import CalendarUsersFieldView from 'crm:views/calendar/fields/users';

export default class TimelineSharedOptionsModalView extends ModalView {

    className = 'dialog dialog-record'

    templateContent = `
        <div class="record-container no-side-margin">{{{record}}}</div>
    `

    /**
     * @private
     * @type {EditForModalRecordView}
     */
    recordView

    /**
     *
     * @param {{
     *     users: {id: string, name: string}[],
     *     onApply: function({
     *         users: {id: string, name: string}[],
     *     }),
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    setup() {
        this.buttonList = [
            {
                name: 'save',
                label: 'Save',
                style: 'primary',
                onClick: () => this.actionSave(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
                onClick: () => this.actionClose(),
            },
        ];

        this.headerText = this.translate('timeline', 'modes', 'Calendar') + ' · ' +
            this.translate('Shared Mode Options', 'labels', 'Calendar')

        const users = this.options.users;

        const userIdList = [];
        const userNames = {};

        users.forEach(item => {
            userIdList.push(item.id);

            userNames[item.id] = item.name;
        });

        this.model = new Model({
            usersIds: userIdList,
            usersNames: userNames,
        });

        this.recordView = new EditForModalRecordView({
            model: this.model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new CalendarUsersFieldView({
                                    name: 'users',
                                }),
                            },
                            false
                        ]
                    ]
                }
            ]
        });

        this.assignView('record', this.recordView);
    }

    /**
     * @private
     */
    actionSave() {
        const data = this.recordView.processFetch();

        if (this.recordView.validate()) {
            return;
        }

        /** @type {{id: string, name: string}[]} */
        const users = [];

        const userIds = this.model.attributes.usersIds || [];

        userIds.forEach(id => {
            users.push({
                id: id,
                name: (data.usersNames || {})[id] || id
            });
        });

        this.options.onApply({users: users})

        this.close();
    }
}
