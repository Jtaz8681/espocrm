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

import SidePanelView from 'views/record/panels/side';

export default class extends SidePanelView {

    setupFields() {
        super.setupFields();

        this.fieldList.push({
            name: 'icsEventDateStart',
            readOnly: true,
            labelText: this.translate('dateStart', 'fields', 'Meeting'),
        });

        this.fieldList.push({
            name: 'createdEvent',
            readOnly: true,
        });

        this.fieldList.push({
            name: 'createEvent',
            readOnly: true,
            noLabel: true,
        });

        this.controlEventField();

        this.listenTo(this.model, 'change:icsEventData', this.controlEventField, this);
        this.listenTo(this.model, 'change:createdEventId', this.controlEventField, this);
    }

    /**
     * @private
     */
    controlEventField() {
        if (!this.model.get('icsEventData')) {
            this.recordViewObject.hideField('createEvent');
            this.recordViewObject.showField('createdEvent');

            return;
        }

        const eventData = this.model.get('icsEventData');

        if (eventData.createdEvent) {
            this.recordViewObject.hideField('createEvent');
            this.recordViewObject.showField('createdEvent');

            return;
        }

        if (!this.model.get('createdEventId')) {
            this.recordViewObject.hideField('createdEvent');
            this.recordViewObject.showField('createEvent');

            return;
        }

        this.recordViewObject.hideField('createEvent');
        this.recordViewObject.showField('createdEvent');
    }
}
