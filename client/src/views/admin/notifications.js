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

    layoutName = 'notifications'

    saveAndContinueEditingAction = false

    dynamicLogicDefs = {
        fields: {
            assignmentEmailNotificationsEntityList: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isTrue',
                            attribute: 'assignmentEmailNotifications',
                        }
                    ],
                },
            },
            adminNotificationsNewVersion: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isTrue',
                            attribute: 'adminNotifications',
                        }
                    ],
                },
            },
            adminNotificationsNewExtensionVersion: {
                visible: {
                    conditionGroup: [
                        {
                            type: 'isTrue',
                            attribute: 'adminNotifications',
                        }
                    ],
                },
            },
        },
    }

    setup() {
        super.setup();

        this.controlStreamEmailNotificationsEntityList();

        this.listenTo(this.model, 'change', (model) => {
            if (model.hasChanged('streamEmailNotifications') || model.hasChanged('portalStreamEmailNotifications')) {
                this.controlStreamEmailNotificationsEntityList();
            }
        });
    }

    controlStreamEmailNotificationsEntityList() {
        if (this.model.get('streamEmailNotifications') || this.model.get('portalStreamEmailNotifications')) {
            this.showField('streamEmailNotificationsEntityList');
            this.showField('streamEmailNotificationsTypeList');
        } else {
            this.hideField('streamEmailNotificationsEntityList');
            this.hideField('streamEmailNotificationsTypeList');
        }
    }
}
