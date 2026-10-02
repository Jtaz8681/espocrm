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

import EditRecordView from 'views/record/edit';

export default class extends EditRecordView {

    scriptAreaHeight = 400

    bottomView = null

    sideView = null

    dropdownItemList = []

    isWide = true
    accessControlDisabled = true
    saveAndContinueEditingAction = false
    saveAndNewAction = false
    shortcutKeyCtrlEnterAction = 'run'

    setup() {
        this.scope = 'Formula';

        this.buttonList = [
            {
                name: 'run',
                label: 'Run',
                style: 'danger',
                title: 'Ctrl+Enter',
                onClick: () => this.actionRun(),
            },
        ];

        const additionalFunctionDataList = [
            {
                "name": "output\\print",
                "insertText": "output\\print(VALUE)"
            },
            {
                "name": "output\\printLine",
                "insertText": "output\\printLine(VALUE)"
            }
        ];

        this.detailLayout = [
            {
                rows: [
                    [
                        false,
                        {
                            name: 'targetType',
                            labelText: this.translate('targetType', 'fields', 'Formula'),
                        },
                        {
                            name: 'target',
                            labelText: this.translate('target', 'fields', 'Formula'),
                        },
                    ]
                ]
            },
            {
                rows: [
                    [
                        {
                            name: 'script',
                            noLabel: true,
                            options: {
                                targetEntityType: this.model.get('targetType'),
                                height: this.scriptAreaHeight,
                                additionalFunctionDataList: additionalFunctionDataList,
                            },
                        },
                    ]
                ]
            },
            {
                name: 'output',
                rows: [
                    [
                        {
                            name: 'errorMessage',
                            labelText: this.translate('error', 'fields', 'Formula'),
                        },
                    ],
                    [
                        {
                            name: 'output',
                            labelText: this.translate('output', 'fields', 'Formula'),
                        },
                    ]
                ]
            },
        ];

        super.setup();

        if (!this.model.get('targetType')) {
            this.hideField('target');
        }
        else {
            this.showField('target');
        }

        this.controlTargetTypeField();
        this.listenTo(this.model, 'change:targetId', () => this.controlTargetTypeField());

        this.controlOutputField();
        this.listenTo(this.model, 'change', () => this.controlOutputField());
    }

    controlTargetTypeField() {
        if (this.model.get('targetId')) {
            this.setFieldReadOnly('targetType');

            return;
        }

        this.setFieldNotReadOnly('targetType');
    }

    controlOutputField() {
        if (this.model.get('errorMessage')) {
            this.showField('errorMessage');
        }
        else {
            this.hideField('errorMessage');
        }
    }

    actionRun() {
        this.model.trigger('run');
    }
}
