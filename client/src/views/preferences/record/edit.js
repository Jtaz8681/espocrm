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

class PreferencesEditRecordView extends EditRecordView {

    sideView = null
    saveAndContinueEditingAction = false

    buttonList = [
        {
            name: 'save',
            label: 'Save',
            style: 'primary',
        },
        {
            name: 'cancel',
            label: 'Cancel',
        }
    ]

    setup() {
        this.dynamicLogicDefs = Espo.Utils.cloneDeep(this.getMetadata().get(`logicDefs.Preferences`));

        super.setup();

        const model = /** @type {import('models/preferences').default} */this.model;

        this.addDropdownItem({
            name: 'reset',
            text: this.getLanguage().translate('Reset to Default', 'labels', 'Admin'),
            style: 'danger',
            onClick: () => this.actionReset(),
        });

        const forbiddenEditFieldList = this.getAcl().getScopeForbiddenFieldList('Preferences', 'edit');

        if (!forbiddenEditFieldList.includes('dashboardLayout') && !model.isPortal()) {
            this.addDropdownItem({
                name: 'resetDashboard',
                text: this.getLanguage().translate('Reset Dashboard to Default', 'labels', 'Preferences'),
                onClick: () => this.actionResetDashboard(),
            });
        }

        if (model.isPortal()) {
            this.layoutName = 'detailPortal';
        }

        if (this.model.id === this.getUser().id) {
            const preferencesModel = this.getPreferences();

            this.on('save', (a, attributeList) => {
                const data = this.model.getClonedAttributes();

                delete data['smtpPassword'];

                preferencesModel.set(data);
                preferencesModel.trigger('update', attributeList);
            });
        }

        if (!this.getUser().isAdmin() || model.isPortal()) {
            this.hidePanel('dashboard');
            this.hideField('dashboardLayout');
        }

        this.controlFollowCreatedEntityListVisibility();
        this.listenTo(this.model, 'change:followCreatedEntities', this.controlFollowCreatedEntityListVisibility);

        this.controlColorsField();
        this.listenTo(this.model, 'change:scopeColorsDisabled', () => this.controlColorsField());

        let hideNotificationPanel = true;

        if (!this.getConfig().get('assignmentEmailNotifications') || model.isPortal()) {
            this.hideField('receiveAssignmentEmailNotifications', true);
            this.hideField('assignmentEmailNotificationsIgnoreEntityTypeList', true);
        } else {
            hideNotificationPanel = false;
        }

        if ((this.getConfig().get('assignmentEmailNotificationsEntityList') || []).length === 0) {
            this.hideField('assignmentEmailNotificationsIgnoreEntityTypeList', true);
        }

        if (
            (this.getConfig().get('assignmentNotificationsEntityList') || []).length === 0 ||
            model.isPortal()
        ) {
            this.hideField('assignmentNotificationsIgnoreEntityTypeList');
        } else {
            hideNotificationPanel = false;
        }

        if (this.getConfig().get('emailForceUseExternalClient')) {
            this.hideField('emailUseExternalClient');
        }

        if (!this.getConfig().get('mentionEmailNotifications') || model.isPortal()) {
            this.hideField('receiveMentionEmailNotifications');
        } else {
            hideNotificationPanel = false;
        }

        if (!this.getConfig().get('streamEmailNotifications') && !model.isPortal()) {
            this.hideField('receiveStreamEmailNotifications');
        } else if (!this.getConfig().get('portalStreamEmailNotifications') && model.isPortal()) {
            this.hideField('receiveStreamEmailNotifications');
        } else {
            hideNotificationPanel = false;
        }

        if (hideNotificationPanel) {
            this.hidePanel('notifications');
        }

        if (this.getConfig().get('userThemesDisabled')) {
            this.hideField('theme');
        }

        this.on('save', /** Record */initialAttributes => {
            if (
                this.model.get('language') !== initialAttributes.language ||
                this.model.get('theme') !== initialAttributes.theme ||
                (this.model.get('themeParams') || {}).navbar !== (initialAttributes.themeParams || {}).navbar ||
                this.model.get('pageContentWidth') !== initialAttributes.pageContentWidth
            ) {
                this.setConfirmLeaveOut(false);

                window.location.reload();
            }
        });
    }

    controlFollowCreatedEntityListVisibility() {
        if (!this.model.get('followCreatedEntities')) {
            this.showField('followCreatedEntityTypeList');
        } else {
            this.hideField('followCreatedEntityTypeList');
        }
    }

    controlColorsField() {
        if (this.model.get('scopeColorsDisabled')) {
            this.hideField('tabColorsDisabled');
        } else {
            this.showField('tabColorsDisabled');
        }
    }

    actionReset() {
        this.confirm(this.translate('resetPreferencesConfirmation', 'messages'), () => {
            Espo.Ajax
                .deleteRequest(`Preferences/${this.model.id}`)
                .then(data => {
                    Espo.Ui.success(this.translate('resetPreferencesDone', 'messages'));

                    this.model.set(data);

                    for (const attribute in data) {
                        this.setInitialAttributeValue(attribute, data[attribute]);
                    }

                    this.getPreferences().set(this.model.getClonedAttributes());
                    this.getPreferences().trigger('update');

                    this.setIsNotChanged();
                });
        });
    }

    actionResetDashboard() {
        this.confirm(this.translate('confirmation', 'messages'), () => {
            Espo.Ajax.postRequest('Preferences/action/resetDashboard', {id: this.model.id})
                .then(data =>  {
                    const isChanged = this.isChanged;

                    Espo.Ui.success(this.translate('Done'));

                    this.model.set(data);

                    for (const attribute in data) {
                        this.setInitialAttributeValue(attribute, data[attribute]);
                    }

                    this.getPreferences().set(this.model.getClonedAttributes());
                    this.getPreferences().trigger('update');

                    if (!isChanged) {
                        this.setIsNotChanged();
                    }
                });
        });
    }

    exit(after) {
        if (after === 'cancel') {
            let url = `#User/view/${this.model.id}`;

            if (!this.getAcl().checkModel(this.getUser())) {
                url = '#';
            }

            this.getRouter().navigate(url, {trigger: true});
        }
    }

    handleShortcutKeyCtrlS(e) {
        this.handleShortcutKeyCtrlEnter(e);
    }
}

export default PreferencesEditRecordView;
