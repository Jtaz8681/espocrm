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
import NoteReactionsView from 'views/stream/reactions';

class PostNoteStreamView extends NoteStreamView {

    template = 'stream/notes/post'
    messageName = 'post'
    isEditable = true
    isRemovable = true

    data() {
        const data = super.data();

        data.showAttachments = !!(this.model.get('attachmentsIds') || []).length;
        data.showPost = !!this.model.get('post');
        data.isInternal = this.isInternal;

        data.isPinned = this.isThis && this.model.get('isPinned') && this.model.collection &&
            !this.model.collection.pinnedList;

        return data;
    }

    setup() {
        this.addActionHandler('react', (e, target) => this.react(target.dataset.type));
        this.addActionHandler('unReact', (e, target) => this.unReact(target.dataset.type));

        this.createField('post', null, null, 'views/stream/fields/post');

        this.createField('attachments', 'attachmentMultiple', {}, 'views/stream/fields/attachment-multiple', {
            previewSize: this.options.isNotification || this.options.isUserStream ? 'small' : 'medium'
        });

        this.isInternal = this.model.get('isInternal');

        this.setupReactions();

        if (!this.model.get('post') && this.model.get('parentId')) {
            this.messageName = 'attach';

            if (this.isThis) {
                this.messageName += 'This';
            }
        }

        this.listenTo(this.model, 'change', () => {
            if (this.model.hasChanged('post') || this.model.hasChanged('attachmentsIds')) {
                this.reRender();
            }
        });

        if (this.messageName === 'postThis') {
            this.messageTemplate = '{user}';
        }

        if (this.model.get('parentId')) {
            this.createMessage();

            return;
        }

        if (this.model.get('isGlobal')) {
            this.messageName = 'postTargetAll';
            this.createMessage();

            return;
        }

        if (this.model.has('teamsIds') && this.model.get('teamsIds').length) {
            const teamIdList = this.model.get('teamsIds');
            const teamNameHash = this.model.get('teamsNames') || {};

            this.messageName = 'postTargetTeam';

            if (teamIdList.length > 1) {
                this.messageName = 'postTargetTeams';
            }

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

        if (this.model.has('portalsIds') && this.model.get('portalsIds').length) {
            const portalIdList = this.model.get('portalsIds');
            const portalNameHash = this.model.get('portalsNames') || {};

            this.messageName = 'postTargetPortal';

            if (portalIdList.length > 1) {
                this.messageName = 'postTargetPortals';
            }

            const portalHtmlList = [];

            portalIdList.forEach(portalId =>{
                const portalName = portalNameHash[portalId];

                if (!portalName) {
                    return;
                }

                portalHtmlList.push(
                    $('<a>')
                        .attr('href', '#Portal/view/' + portalId)
                        .text(portalName)
                        .get(0).outerHTML
                )
            });

            this.messageData['html:target'] = portalHtmlList.join(', ');

            this.createMessage();

            return;
        }

        if (!this.model.has('usersIds') || !this.model.get('usersIds').length) {
            this.createMessage();

            return;
        }

        const userIdList = this.model.get('usersIds');
        const userNameHash = this.model.get('usersNames') || {};

        this.messageName = 'postTarget';

        if (userIdList.length === 1 && userIdList[0] === this.model.get('createdById')) {
            this.messageName = 'postTargetSelf';
            this.createMessage();

            return;
        }

        const userHtmlList = [];

        userIdList.forEach(userId => {
            if (userId === this.getUser().id) {
                this.messageName = 'postTargetYou';

                if (userIdList.length > 1) {
                    if (userId === this.model.get('createdById')) {
                        this.messageName = 'postTargetSelfAndOthers';
                    } else {
                        this.messageName = 'postTargetYouAndOthers';
                    }
                }

                return;
            }

            if (userId === this.model.get('createdById')) {
                this.messageName = 'postTargetSelfAndOthers';

                return;
            }

            const userName = userNameHash[userId];

            if (!userName) {
                return;
            }

            userHtmlList.push(
                $('<a>')
                    .attr('href', '#User/view/' + userId)
                    .attr('data-scope', 'User')
                    .attr('data-id', userId)
                    .text(userName)
                    .get(0).outerHTML
            );
        });

        this.messageData['html:target'] = userHtmlList.join(', ');

        this.createMessage();
    }

    /**
     * @private
     * @param {string} type
     */
    async react(type) {
        Espo.Ui.notifyWait();

        const previousMyReactions = this.model.attributes.myReactions;
        const previousReactionCounts = this.model.attributes.reactionCounts;

        const reactionCounts = {...previousReactionCounts};

        if (!(type in reactionCounts)) {
            reactionCounts[type] = 0;
        }

        reactionCounts[type]++;

        this.model.set({
            myReactions: [type],
            reactionCounts: reactionCounts,
        }, {userReaction: true});

        try {
            await Espo.Ajax.postRequest(`Note/${this.model.id}/myReactions/${type}`);
        } catch (e) {
            this.model.set({
                myReactions: previousMyReactions,
                reactionCounts: previousReactionCounts,
            }, {userReaction: true});

            return;
        }

        Espo.Ui.success(this.translate('Reacted') + ' · ' + this.translate(type, 'reactions'));

        await this.model.fetch({userReaction: true, keepRowActions: true});
    }

    /**
     * @private
     * @param {string} type
     */
    async unReact(type) {
        Espo.Ui.notifyWait();

        const previousMyReactions = this.model.attributes.myReactions;
        const previousReactionCounts = this.model.attributes.reactionCounts;

        const reactionCounts = {...previousReactionCounts};

        if (!(type in reactionCounts)) {
            reactionCounts[type] = 0;
        }

        reactionCounts[type]--;

        this.model.set({
            myReactions: [],
            reactionCounts: reactionCounts,
        }, {userReaction: true});

        try {
            await Espo.Ajax.deleteRequest(`Note/${this.model.id}/myReactions/${type}`);
        } catch (e) {
            this.model.set({
                myReactions: previousMyReactions,
                reactionCounts: previousReactionCounts,
            }, {userReaction: true});

            return;
        }

        Espo.Ui.warning(this.translate('Reaction Removed'));

        await this.model.fetch({userReaction: true, keepRowActions: true});
    }

    /**
     * @private
     */
    setupReactions() {
        const view = new NoteReactionsView({model: this.model});
        this.assignView('reactions', view, '.reactions-container');

        this.listenTo(this.model, 'change:reactionCounts change:myReactions', () => view.reRenderWhenNoPopover());
    }
}

export default PostNoteStreamView;
