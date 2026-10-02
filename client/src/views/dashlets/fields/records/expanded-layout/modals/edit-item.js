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
import BoolFieldView from 'views/fields/bool';

export default class ExpandedLayoutEditItemModalFieldView extends ModalView {

    // language=Handlebars
    templateContent = `
        <div class="record-container no-side-margin">{{{record}}}</div>
    `

    /**
     * @private
     * @type {EditForModalRecordView}
     */
    recordView

    /**
     * @private
     * @type {Model}
     */
    formModel

    /**
     * @param {{
     *     onApply: function({soft: boolean, small: boolean}),
     *     label: string,
     *     data: {
     *         soft: boolean,
     *         small: boolean,
     *     },
     * }} options
     */
    constructor(options) {
        super();

        this.options = options;
    }

    setup() {
        this.headerText = this.translate('Edit') + ' · ' + this.options.label;

        this.formModel = new Model();
        this.formModel.setMultiple({...this.options.data});

        this.recordView = new EditForModalRecordView({
            model: this.formModel,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new BoolFieldView({
                                    name: 'soft',
                                    labelText: this.translate('soft', 'otherFields', 'DashletOptions'),
                                }),
                            },
                            {
                                view: new BoolFieldView({
                                    name: 'small',
                                    labelText: this.translate('small', 'otherFields', 'DashletOptions'),
                                }),
                            },
                        ],
                    ]
                }
            ]
        });

        this.assignView('record', this.recordView);

        this.buttonList = [
            {
                name: 'apply',
                style: 'danger',
                label: 'Apply',
                onClick: () => this.actionApply(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
                onClick: () => this.actionClose(),
            },
        ]
    }

    /**
     * @private
     */
    actionApply() {
        if (this.recordView.validate()) {
            return;
        }

        this.options.onApply({
            soft: this.formModel.attributes.soft,
            small: this.formModel.attributes.small,
        });

        this.close();
    }
}
