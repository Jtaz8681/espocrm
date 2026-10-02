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

import NoteStreamView from 'views/stream/note';

/** @module views/stream/notes/create */

class CreateNoteStreamView extends NoteStreamView {

    template = 'stream/notes/create'
    assigned = false
    messageName = 'create'
    isRemovable = false

    data() {
        return {
            ...super.data(),
            statusText: this.statusText,
            statusStyle: this.statusStyle,
            iconHtml: this.getIconHtml(),
        };
    }

    setup() {
        if (this.model.get('data')) {
            this.setupData();
        }

        this.createMessage();
    }

    /**
     * @typedef {{
     *    assignedUserId?: string,
     *     assignedUserName?: string,
     *     assignedUsers?: {id: string, name: string|null}[],
     *     statusField?: string,
     *     statusValue?: string|null,
     *     statusStyle?: string|null,
     * }} module:views/stream/notes/create~data
     */

    setupData() {
        /** @type {module:views/stream/notes/create~data} */
        const data = this.model.get('data') || {};

        this.setupUsersData();

        const parentType = this.model.attributes.parentType;

        if (data.statusValue != null) {
            const statusField = this.statusField = this.getMetadata().get(`scopes.${parentType}.statusField`) ?? '';
            const statusValue = data.statusValue;

            this.statusStyle = this.getMetadata()
                .get(`entityDefs.${parentType}.fields.${statusField}.style.${statusValue}`) ||
                'default';

            this.statusText = this.getLanguage()
                .translateOption(statusValue, statusField, parentType);
        }
    }

    setupUsersData() {
        /** @type {module:views/stream/notes/create~data} */
        const data = this.model.get('data') || {};

        this.assignedUserId = data.assignedUserId || null;
        this.assignedUserName = data.assignedUserName || data.assignedUserId || null;

        if (data.assignedUsers) {
            if (data.assignedUsers.length === 1) {
                this.assignedUserId = data.assignedUsers[0].id;
                this.assignedUserName = data.assignedUsers[0].name;
            } else if (data.assignedUsers.length > 1) {
                this.setupUsersDataMulti();

                if (this.isThis) {
                    this.messageName += 'This';
                }

                return;
            }
        }

        this.messageData['assignee'] =
            $('<span>')
                .addClass('nowrap name-avatar')
                .append(
                    this.getHelper().getAvatarHtml(this.assignedUserId, 'small', 16, 'avatar-link'),
                    $('<a>')
                        .attr('href', `#User/view/${this.assignedUserId}`)
                        .text(this.assignedUserName)
                        .attr('data-scope', 'User')
                        .attr('data-id', this.assignedUserId)
                );

        let isYou = false;

        if (this.isUserStream) {
            if (this.assignedUserId === this.getUser().id) {
                isYou = true;
            }
        }

        if (this.assignedUserId) {
            this.messageName = 'createAssigned';

            if (this.isThis) {
                this.messageName += 'This';

                if (this.assignedUserId === this.model.attributes.createdById) {
                    this.messageName += 'Self';
                }
            } else {
                if (this.assignedUserId === this.model.attributes.createdById) {
                    this.messageName += 'Self';
                } else if (isYou) {
                    this.messageName += 'You';
                }
            }
        }
    }

    setupUsersDataMulti() {
        this.messageName = 'createAssigned';

        /** @type {module:views/stream/notes/create~data} */
        const data = this.model.get('data') || {};

        this.messageData['assignee'] = this.createUsersElement(data.assignedUsers);
    }

    /**
     * @private
     * @param {{id: string, name: ?string}[]} users
     * @return {HTMLElement}
     */
    createUsersElement(users) {
        const wrapper = document.createElement('span');

        users.forEach((it, i) => {
            const a = document.createElement('a');
            a.href = `#User/view/${it.id}`;
            a.text = it.name || it.id;
            a.dataset.id = it.id;
            a.dataset.scope = 'User';

            const span = document.createElement('span');
            span.className = 'nowrap name-avatar';
            span.innerHTML = this.getHelper().getAvatarHtml(it.id, 'small', 16, 'avatar-link');
            span.appendChild(a);

            wrapper.appendChild(span);

            if (i < users.length - 1) {
                wrapper.appendChild(document.createTextNode(', '));
            }
        });

        return wrapper;
    }
}

export default CreateNoteStreamView;

