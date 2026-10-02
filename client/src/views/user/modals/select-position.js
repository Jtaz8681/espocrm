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
import EnumFieldView from 'views/fields/enum';

export default class UserSelectPositionModalView extends ModalView {

    templateContent = '<div class="record no-side-margin">{{{record}}}</div>'

    className = 'dialog dialog-record'

    shortcutKeys = {}

    /**
     * @param {{
     *     positionList: string[],
     *     position: string|null,
     *     name: string,
     *     onApply: function(string|null),
     * }} options
     */
    constructor(options) {
        super(options);

        /** @private */
        this.props = options;
    }

    setup() {
        this.headerText =
            this.translate('changePosition', 'actions', 'User') + ' · ' +
            this.props.name;

        this.buttonList = [
            {
                name: 'save',
                label: 'Save',
                style: 'primary',
                onClick: () => this.apply(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            }
        ];

        this.model = new Model();
        this.model.setMultiple({position: this.props.position});

        this.recordView = new EditForModalRecordView({
            model: this.model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new EnumFieldView({
                                    name: 'position',
                                    params: {
                                        options: ['', ...this.props.positionList],
                                    },
                                    labelText: this.translate('teamRole', 'fields', 'User'),
                                }),
                            },
                            false
                        ]
                    ]
                }
            ]
        });

        this.assignView('record', this.recordView, '.record');

        this.shortcutKeys['Control+Enter'] = e => {
            e.preventDefault();
            e.stopPropagation();

            this.apply();
        }
    }

    /**
     * @private
     */
    apply() {
        if (this.recordView.validate()) {
            return;
        }

        this.props.onApply(this.model.attributes.position);
        this.close();
    }

    onBackdropClick() {
        if (this.recordView.hasChanged()) {
            return;
        }

        this.close();
    }
}
