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

import DetailRecordView from 'views/record/detail';

export default class extends DetailRecordView {

    hasModifyDetailLayout = true

    setup() {
        super.setup();

        this.setupFieldsBehaviour();
        this.initSslFieldListening();
    }

    modifyDetailLayout(layout) {
        layout.filter(panel => panel.tabLabel === '$label:SMTP')
            .forEach(panel => {
                panel.rows.forEach(row => {
                    row.forEach(item => {
                        const labelText = this.translate(item.name, 'fields', 'InboundEmail');

                        if (labelText && labelText.indexOf('SMTP ') === 0) {
                            item.labelText = Espo.Utils.upperCaseFirst(labelText.substring(5));
                        }
                    });
                })
            });
    }

    wasFetched() {
        if (!this.model.isNew()) {
            return !!((this.model.get('fetchData') || {}).lastUID);
        }

        return false;
    }

    initSmtpFieldsControl() {
        this.controlSmtpFields();
        this.listenTo(this.model, 'change:useSmtp', this.controlSmtpFields, this);
        this.listenTo(this.model, 'change:smtpAuth', this.controlSmtpFields, this);
    }

    controlSmtpFields() {
        if (this.model.get('useSmtp')) {
            this.showField('smtpHost');
            this.showField('smtpPort');
            this.showField('smtpAuth');
            this.showField('smtpSecurity');
            this.showField('smtpTestSend');
            this.showField('fromName');
            this.showField('smtpIsShared');
            this.showField('smtpIsForMassEmail');

            this.setFieldRequired('smtpHost');
            this.setFieldRequired('smtpPort');

            this.controlSmtpAuthField();

            return;
        }

        this.hideField('smtpHost');
        this.hideField('smtpPort');
        this.hideField('smtpAuth');
        this.hideField('smtpUsername');
        this.hideField('smtpPassword');
        this.hideField('smtpAuthMechanism');
        this.hideField('smtpSecurity');
        this.hideField('smtpTestSend');
        this.hideField('fromName');
        this.hideField('smtpIsShared');
        this.hideField('smtpIsForMassEmail');

        this.setFieldNotRequired('smtpHost');
        this.setFieldNotRequired('smtpPort');
        this.setFieldNotRequired('smtpUsername');
    }

    controlSmtpAuthField() {
        if (this.model.get('smtpAuth')) {
            this.showField('smtpUsername');
            this.showField('smtpPassword');
            this.showField('smtpAuthMechanism');
            this.setFieldRequired('smtpUsername');

            return;
        }

        this.hideField('smtpUsername');
        this.hideField('smtpPassword');
        this.hideField('smtpAuthMechanism');
        this.setFieldNotRequired('smtpUsername');
    }

    controlStatusField() {
        const list = ['username', 'port', 'host', 'monitoredFolders'];

        if (this.model.get('status') === 'Active' && this.model.get('useImap')) {
            list.forEach(item => {
                this.setFieldRequired(item);
            });

            return;
        }

        list.forEach(item => {
            this.setFieldNotRequired(item);
        });
    }

    setupFieldsBehaviour() {
        this.controlStatusField();

        this.listenTo(this.model, 'change:status', (model, value, o) => {
            if (o.ui) {
                this.controlStatusField();
            }
        });

        this.listenTo(this.model, 'change:useImap', (model, value, o) => {
            if (o.ui) {
                this.controlStatusField();
            }
        });

        if (this.wasFetched()) {
            this.setFieldReadOnly('fetchSince');
        } else {
            this.setFieldNotReadOnly('fetchSince');
        }

        this.initSmtpFieldsControl();

        const handleRequirement = (model) => {
            if (model.get('createCase')) {
                this.showField('caseDistribution');
            } else {
                this.hideField('caseDistribution');
            }

            if (
                model.get('createCase') &&
                ['Round-Robin', 'Least-Busy'].indexOf(model.get('caseDistribution')) !== -1
            ) {
                this.setFieldRequired('team');
                this.showField('targetUserPosition');
            } else {
                this.setFieldNotRequired('team');
                this.hideField('targetUserPosition');
            }

            if (model.get('createCase') && 'Direct-Assignment' === model.get('caseDistribution')) {
                this.setFieldRequired('assignToUser');
                this.showField('assignToUser');
            } else {
                this.setFieldNotRequired('assignToUser');
                this.hideField('assignToUser');
            }

            if (model.get('createCase') && model.get('createCase') !== '') {
                this.showField('team');
            } else {
                this.hideField('team');
            }
        };

        this.listenTo(this.model, 'change:createCase', (model, value, o) => {
            handleRequirement(model);

            if (!o.ui) {
                return;
            }

            if (!model.get('createCase')) {
                this.model.set({
                    caseDistribution: '',
                    teamId: null,
                    teamName: null,
                    assignToUserId: null,
                    assignToUserName: null,
                    targetUserPosition: '',
                });
            }
        });

        handleRequirement(this.model);

        this.listenTo(this.model, 'change:caseDistribution', (model, value, o) => {
            handleRequirement(model);

            if (!o.ui) {
                return;
            }

            setTimeout(() => {
                if (!this.model.get('caseDistribution')) {
                    this.model.set({
                        assignToUserId: null,
                        assignToUserName: null,
                        targetUserPosition: ''
                    });

                    return;
                }

                if (this.model.get('caseDistribution') === 'Direct-Assignment') {
                    this.model.set({
                        targetUserPosition: '',
                    });
                }

                this.model.set({
                    assignToUserId: null,
                    assignToUserName: null,
                });
            }, 10);
        });
    }

    initSslFieldListening() {
        this.listenTo(this.model, 'change:security', (model, value, o) => {
            if (!o.ui) {
                return;
            }

            if (value === 'SSL') {
                this.model.set('port', 993);
            } else {
                this.model.set('port', 143);
            }
        });

        this.listenTo(this.model, 'change:smtpSecurity', (model, value, o) => {
            if (!o.ui) {
                return;
            }

            if (value === 'SSL') {
                this.model.set('smtpPort', 465);
            } else if (value === 'TLS') {
                this.model.set('smtpPort', 587);
            } else {
                this.model.set('smtpPort', 25);
            }
        });
    }
}
