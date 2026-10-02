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

    templateContent = '<div class="record no-side-margin">{{{record}}}</div>'

    className = 'dialog dialog-record'

    shortcutKeys = {
        'Control+Enter': 'apply',
    }

    setup() {
        this.buttonList = [
            {
                name: 'apply',
                label: 'Apply',
                style: 'danger',
                onClick: () => this.actionApply(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.headerHtml = '&nbsp';

        this.userModel = this.options.userModel;

        const model = this.model = new Model();
        model.name = 'UserSecurity';

        model.setDefs({
            fields: {
                'password': {
                    type: 'password',
                    required: true,
                },
            }
        });

        this.createView('record', 'views/record/edit-for-modal', {
            scope: 'None',
            selector: '.record',
            model: this.model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                name: 'password',
                                labelText: this.translate('yourPassword', 'fields', 'User'),
                                params: {
                                    readyToChange: true,
                                }
                            },
                            false
                        ]
                    ]
                }
            ],
        });
    }

    /**
     * @return {import('views/record/edit').default}
     */
    getRecordView() {
        return this.getView('record');
    }

    actionApply() {
        const data = this.getRecordView().processFetch();

        if (!data) {
            return;
        }

        this.trigger('proceed', data);
    }
}
