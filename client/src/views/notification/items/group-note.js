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

import BaseNotificationItemView from 'views/notification/items/base';
import Ajax from 'ajax';
import NotificationContainerFieldView from 'views/notification/fields/container';

// noinspection JSUnusedGlobalSymbols
export default class GroupNoteNotificationItemView extends BaseNotificationItemView {

    // language=Handlebars
    templateContent = `
        <div class="stream-head-container">
            {{#if iconClass}}
                <div class="pull-left stream-head-left-icon-container">
                    <span
                        class=" {{iconClass}} text-muted action icon"
                        style="
                            cursor: pointer;
                            {{#if color}} color: {{color}}; {{/if}}
                        "
                        title="{{translate 'View'}}"
                        data-action="quickView"
                        data-id="{{relatedParentId}}"
                        data-scope="{{relatedParentType}}"
                    ></span>
                </div>
            {{/if}}
            <div class="stream-head-text-container">
                <span class="text-muted message">{{{message}}}</span>
            </div>
        </div>
        {{~#if isFeatured~}}
            <div class="stream-post-container">
                <span class="label label-default">{{translate 'Assigned'}}</span>
            </div>
        {{~/if~}}
        <div class="stream-date-container">
            <span class="text-muted small">{{{createdAt}}}</span>
        </div>
    `

    data() {
        const relatedParentType = this.model.attributes.relatedParentType
        const iconClass = this.getMetadata().get(`clientDefs.${relatedParentType}.iconClass`);
        const color = this.getMetadata().get(`clientDefs.${relatedParentType}.color`);

        return {
            ...super.data(),
            relatedParentId: this.model.attributes.relatedParentId,
            relatedParentType: this.model.attributes.relatedParentType,
            isFeatured: this.model.attributes.isFeatured,
            iconClass,
            color,
        };
    }

    setup() {
        const relatedParentType = this.model.attributes.relatedParentType;

        if (relatedParentType) {
            this.messageData['entityType'] = this.translateEntityType(relatedParentType);
        }

        this.messageData['entity'] = 'field:relatedParent';

        const newCount = this.model.attributes.groupedUnreadCount ?? 0;

        this.messageData['number'] = newCount.toString();

        this.messageName = newCount > 1 ? 'groupUpdatesMultiple' : 'groupUpdatesOne';

        if (newCount === 0) {
            this.messageName = 'groupUpdates';
        }

        this.createMessage();

        this.addHandler('click',
            '> .stream-head-container > .stream-head-text-container [data-key="entity"] a', () => {
                this.markGroupRead();
            });
    }

    /**
     * @private
     */
    async markGroupRead() {
        if (this.model.attributes.read) {
            return;
        }

        await Ajax.postRequest(`Notification/group/${this.model.id}/markRead`);

        this.model.set('read', true, {sync: true});

        this.triggerUpdateRead();
    }

    /**
     * @private
     */
    triggerUpdateRead() {
        const parentView = this.getParentView();

        if (!(parentView instanceof NotificationContainerFieldView)) {
            return;
        }

        parentView.triggerUpdateRead();
    }
}
