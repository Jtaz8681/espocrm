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

/** @module views/stream/notes/assign */

class AssignNoteStreamView extends NoteStreamView {

    template = 'stream/notes/assign'
    messageName = 'assign'

    init() {
        if (this.getUser().isAdmin()) {
            this.isRemovable = true;
        }

        super.init();
    }

    data() {
        return {
            ...super.data(),
            iconHtml: this.getIconHtml(),
        };
    }

    /**
     * @typedef {{
     *    assignedUserId?: string,
     *     assignedUserName?: string,
     *     addedAssignedUsers?: {id: string, name: string|null}[],
     *     removedAssignedUsers?: {id: string, name: string|null}[],
     * }} module:views/stream/notes/assign~data
     */

    setup() {
        this.setupData();

        this.createMessage();
    }

    setupData() {
        /** @type {module:views/stream/notes/assign~data} */
        const data = this.model.get('data') || {};

        this.assignedUserId = data.assignedUserId || null;
        this.assignedUserName = data.assignedUserName || data.assignedUserId || null;

        if (data.addedAssignedUsers) {
            this.setupDataMulti(data);

            if (this.isThis) {
                this.messageName += 'This';
            }

            return;
        }

        this.messageData['assignee'] =
            $('<span>')
                .addClass('nowrap name-avatar')
                .append(
                    this.getHelper().getAvatarHtml(data.assignedUserId, 'small', 16, 'avatar-link'),
                    $('<a>')
                        .attr('href', `#User/view/${data.assignedUserId}`)
                        .text(this.assignedUserName)
                        .attr('data-scope', 'User')
                        .attr('data-id', data.assignedUserId)
                );

        if (this.isUserStream) {
            if (this.assignedUserId) {
                if (this.assignedUserId === this.model.get('createdById')) {
                    this.messageName += 'Self';
                } else {
                    if (this.assignedUserId === this.getUser().id) {
                        this.messageName += 'You';
                    }
                }
            } else {
                this.messageName += 'Void';
            }

            return;
        }

        if (this.assignedUserId) {
            if (this.assignedUserId === this.model.get('createdById')) {
                this.messageName += 'Self';
            }

            return;
        }

        this.messageName += 'Void';
    }

    /**
     * @private
     * @param {module:views/stream/notes/assign~data} data
     */
    setupDataMulti(data) {
        this.messageName = 'assignMultiAdd';

        const added = data.addedAssignedUsers;
        const removed = data.removedAssignedUsers;

        if (!added || !removed) {
            return;
        }

        if (added.length && removed.length) {
            this.messageName = 'assignMultiAddRemove';
        } else if (removed.length) {
            this.messageName = 'assignMultiRemove';
        }

        if (added.length) {
            this.messageData['assignee'] = this.createUsersElement(added);
        }

        if (removed.length) {
            this.messageData['removedAssignee'] = this.createUsersElement(removed);
        }
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

export default AssignNoteStreamView;
