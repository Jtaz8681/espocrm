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
import ReactionsHelper from 'helpers/misc/reactions';

// noinspection JSUnusedGlobalSymbols
export default class UserReactionNotificationItemView extends BaseNotificationItemView {

    // language=Handlebars
    templateContent = `
        <div class="stream-head-container">
            <div class="pull-left">
                {{{avatar}}}
            </div>
            <div class="stream-head-text-container">
                <span
                    class="{{reactionIconClass}} text-muted action icon"
                    style="cursor: pointer;"
                    title="{{translate 'View'}}"
                    data-action="quickView"
                    data-id="{{noteId}}"
                    data-scope="Note"
                ></span><span class="text-muted message">{{{message}}}</span>
            </div>
        </div>

        <div class="stream-date-container">
            <span class="text-muted small">{{{createdAt}}}</span>
        </div>
    `

    messageName = 'userPostReaction'

    /**
     * @private
     * @type {string|null}
     */
    reactionIconClass

    /**
     * @private
     * @type {string}
     */
    noteId

    data() {
        return {
            ...super.data(),
            reactionIconClass: this.reactionIconClass,
            noteId: this.noteId,
        };
    }

    setup() {
        const data = /** @type {Object.<string, *>} */this.model.attributes.data || {};

        const relatedParentId = this.model.attributes.relatedParentId;
        const relatedParentType = this.model.attributes.relatedParentType;

        this.userId = this.model.attributes.createdById || data.userId;
        this.noteId = this.model.attributes.relatedId;

        const userName = data.userName || this.model.attributes.createdByName;

        this.messageData['type'] = this.translate(data.type, 'reactions');

        const reactionsHelper = new ReactionsHelper();
        this.reactionIconClass = reactionsHelper.getIconClass(data.type);

        const userElement = document.createElement('a');
        userElement.href = `#User/view/${this.model.attributes.createdById}`;
        userElement.dataset.id = this.model.attributes.createdById;
        userElement.dataset.scope = 'User';
        userElement.textContent = userName;

        this.messageData['user'] = userElement;

        if (relatedParentId && relatedParentType) {
            this.messageData['entityType'] = this.translateEntityType(relatedParentType);
            this.messageData['entity'] = 'field:relatedParent';

            this.messageName = 'userPostInParentReaction';
        }

        let postLabel = this.getLanguage().translateOption('Post', 'type', 'Note');

        if (!this.toUpperCaseFirstLetter()) {
            postLabel = Espo.Utils.lowerCaseFirst(postLabel);
        }

        const postElement = document.createElement('a');
        postElement.href = `#Note/view/${this.noteId}`;
        postElement.dataset.id = this.noteId;
        postElement.dataset.scope = 'Note';
        postElement.textContent = postLabel;

        this.messageData['post'] = postElement;

        this.createMessage();
    }
}
