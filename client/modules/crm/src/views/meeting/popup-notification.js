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

import PopupNotificationView from 'views/popup-notification';

class MeetingPopupNotificationView extends PopupNotificationView {

    template = 'crm:meeting/popup-notification'

    type = 'event'
    style = 'primary'
    closeButton = true
    collapseButton = true

    setup() {
        if (!this.notificationData.entityType) {
            return;
        }

        const promise = this.getModelFactory().create(this.notificationData.entityType, model => {
            const field = this.notificationData.dateField;
            const fieldType = model.getFieldParam(field, 'type') || 'base';
            const viewName = this.getFieldManager().getViewName(fieldType);

            model.set(this.notificationData.attributes);

            this.createView('date', viewName, {
                model: model,
                mode: 'detail',
                selector: `.field[data-name="${field}"]`,
                name: field,
                readOnly: true,
            });
        });

        this.wait(promise);
    }

    data() {
        return {
            header: this.translate(this.notificationData.entityType, 'scopeNames'),
            dateField: this.notificationData.dateField,
            ...super.data(),
        };
    }

    onCancel() {
        Espo.Ajax.postRequest('Activities/action/removePopupNotification', {id: this.notificationId});
    }

    getTitle() {
        return this.notificationData.name ??
            this.translate(this.notificationData.entityType, 'scopeNames');
    }
}

export default MeetingPopupNotificationView;
