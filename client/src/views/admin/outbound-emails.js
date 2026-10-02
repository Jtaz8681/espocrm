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

import SettingsEditRecordView from 'views/settings/record/edit';

export default class extends SettingsEditRecordView {

    layoutName = 'outboundEmails'

    saveAndContinueEditingAction = false

    dynamicLogicDefs = {
        fields: {
            smtpUsername: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                        {
                            type: 'isTrue',
                            attribute: 'smtpAuth',
                        }
                    ]
                },
                required: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                        {
                            type: 'isTrue',
                            attribute: 'smtpAuth',
                        }
                    ]
                }
            },
            smtpPassword: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                        {
                            type: 'isTrue',
                            attribute: 'smtpAuth',
                        }
                    ]
                }
            },
            smtpPort: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                    ]
                },
                required: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                    ]
                }
            },
            smtpSecurity: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                    ]
                }
            },
            smtpAuth: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isNotEmpty',
                            attribute: 'smtpServer',
                        },
                    ]
                }
            },
        },
    }

    afterRender() {
        super.afterRender();

        const smtpSecurityField = this.getFieldView('smtpSecurity');

        this.listenTo(smtpSecurityField, 'change', () => {
            const smtpSecurity = smtpSecurityField.fetch()['smtpSecurity'];

            if (smtpSecurity === 'SSL') {
                this.model.set('smtpPort', 465);
            } else if (smtpSecurity === 'TLS') {
                this.model.set('smtpPort', 587);
            } else {
                this.model.set('smtpPort', 25);
            }
        });
    }
}

