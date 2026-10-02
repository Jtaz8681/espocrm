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
import DOMPurify from 'dompurify';
import MailtoHelper from 'helpers/misc/mailto';
import Ui from 'ui';

class MessageNotificationItemView extends BaseNotificationItemView {

    template = 'notification/items/message'

    data() {
        return {
            ...super.data(),
            style: this.style,
        };
    }

    setup() {
        const data = /** @type {Object.<string, *>} */this.model.get('data') || {};

        const messageRaw = this.model.get('message') || data.message || '';

        const message = this.getHelper().transformMarkdownText(messageRaw);

        this.messageTemplate = DOMPurify.sanitize(message, {}).toString();

        this.userId = data.userId;
        this.style = data.style || 'text-muted';

        this.messageData['entityType'] = this.translateEntityType(data.entityType);

        this.messageData['user'] =
            $('<a>')
                .attr('href', '#User/view/' + data.userId)
                .attr('data-id', data.userId)
                .attr('data-scope', 'User')
                .text(data.userName);

        this.messageData['entity'] =
            $('<a>')
                .attr('href', '#' + data.entityType + '/view/' + data.entityId)
                .attr('data-id', data.entityId)
                .attr('data-scope', data.entityType)
                .text(data.entityName);

        this.createMessage();

        this.addActionHandler('mailTo', (_, target) => this.mailTo(target.dataset.emailAddress));
    }

    /**
     * @private
     * @param {string} emailAddress
     * @return {Promise<void>}
     */
    async mailTo(emailAddress) {
        const attributes = {
            status: 'Draft',
            to: emailAddress,
        };

        const helper = new MailtoHelper();

        if (helper.toUse()) {
            document.location.href = helper.composeLink(attributes);

            return;
        }

        Ui.notifyWait();

        const view = await this.createView('dialog', 'views/modals/compose-email', {attributes: attributes});
        await view.render();

        Ui.notify();
    }
}

export default MessageNotificationItemView;
