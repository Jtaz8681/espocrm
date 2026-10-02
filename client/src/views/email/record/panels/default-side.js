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

import DefaultSidePanelView from 'views/record/panels/default-side';

export default class extends DefaultSidePanelView {

    setupFields() {
        super.setupFields();

        this.fieldList.push({
            name: 'isAutoReply',
        });

        this.fieldList.push({
            name: 'hasAttachment',
            view: 'views/email/fields/has-attachment',
            noLabel: true,
        });

        this.controlHasAttachmentField();
        this.listenTo(this.model, 'change:hasAttachment', () => this.controlHasAttachmentField());

        this.controlIsAutoReply();
        this.listenTo(this.model, 'change:isAutoReply', () => this.controlIsAutoReply());
    }

    /**
     * @private
     */
    controlHasAttachmentField() {
        if (this.model.attributes.hasAttachment) {
            this.recordViewObject.showField('hasAttachment');

            return;
        }

        this.recordViewObject.hideField('hasAttachment');
    }

    /**
     * @private
     */
    controlIsAutoReply() {
        if (this.model.attributes.isAutoReply) {
            this.recordViewObject.showField('isAutoReply');

            return;
        }

        this.recordViewObject.hideField('isAutoReply');
    }
}
