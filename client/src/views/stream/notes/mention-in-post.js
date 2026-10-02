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

class MentionInPostNoteStreamView extends NoteStreamView {

    template = 'stream/notes/post'
    messageName = 'mentionInPost'

    data() {
        return {
            ...super.data(),
            showAttachments: !!(this.model.get('attachmentsIds') || []).length,
            showPost: !!this.model.get('post'),
        };
    }

    setup() {
        if (this.model.get('post')) {
            this.createField('post', null, null, 'views/stream/fields/post');
        }

        if ((this.model.get('attachmentsIds') || []).length) {
            this.createField('attachments', 'attachmentMultiple', {}, 'views/stream/fields/attachment-multiple', {
                previewSize: this.options.isNotification ? 'small' : null
            });
        }

        this.messageData['mentioned'] = this.options.userId;

        if (!this.model.get('parentId')) {
            this.messageName = 'mentionInPostTarget';
        }

        if (
            (!this.options.isNotification) &&
            (!this.isUserStream || this.options.userId !== this.getUser().id)
        ) {
            this.createMessage();

            return;
        }

        if (this.model.get('parentId')) {
            this.messageName = 'mentionYouInPost';

            this.createMessage();

            return;
        }

        this.messageName = 'mentionYouInPostTarget';

        if (this.model.get('isGlobal')) {
            this.messageName = 'mentionYouInPostTargetAll';

            this.createMessage();

            return;
        }

        this.messageName = 'mentionYouInPostTarget';

        if (this.model.has('teamsIds') && this.model.get('teamsIds').length) {
            const teamIdList = this.model.get('teamsIds');
            const teamNameHash = this.model.get('teamsNames') || {};

            const teamHtmlList = [];

            teamIdList.forEach(teamId => {
                const teamName = teamNameHash[teamId];

                if (!teamName) {
                    return;
                }

                teamHtmlList.push(
                    $('<a>')
                        .attr('href', '#Team/view/' + teamId)
                        .text(teamName)
                        .get(0).outerHTML
                );
            });

            this.messageData['html:target'] = teamHtmlList.join(', ');

            this.createMessage();

            return;
        }

        if (this.model.has('usersIds') && this.model.get('usersIds').length) {
            const userIdList = this.model.get('usersIds');
            const userNameHash = this.model.get('usersNames') || {};

            if (userIdList.length === 1 && userIdList[0] === this.model.get('createdById')) {
                this.messageName = 'mentionYouInPostTargetNoTarget';
                this.createMessage();

                return;
            }

            const userHtmlList = [];

            userIdList.forEach(userId => {
                const userName = userNameHash[userId];

                if (!userName) {
                    return;
                }

                userHtmlList.push(
                    $('<a>')
                        .attr('href', '#User/view/' + userId)
                        .text(userName)
                        .get(0).outerHTML
                );
            });

            this.messageData['html:target'] = userHtmlList.join(', ');

            this.createMessage();

            return;
        }

        if (this.model.get('targetType') === 'self') {
            this.messageName = 'mentionYouInPostTargetNoTarget';
        }

        this.createMessage();
    }
}

// noinspection JSUnusedGlobalSymbols
export default MentionInPostNoteStreamView;
