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

import DefaultRowActionsView from 'views/record/row-actions/default';
import ReactionsHelper from 'helpers/misc/reactions';
import ReactionsRowActionView from 'views/stream/record/row-actions/reactions/reactions';

class StreamDefaultNoteRowActionsView extends DefaultRowActionsView {

    pinnedMaxCount

    isDetached = false

    /**
     * @private
     * @type {string[]}
     */
    availableReactions

    /**
     * @private
     * @type {ReactionsHelper}
     */
    reactionHelper

    setup() {
        super.setup();

        /** @type import('model').default */
        this.parentModel = this.options.parentModel;

        if (this.options.isThis && this.parentModel) {
            this.listenTo(this.model, 'change:isPinned', () => this.reRender());
            this.listenToOnce(this.parentModel, 'acl-edit-ready', () => this.reRender());

            this.pinnedMaxCount = this.getConfig().get('notePinnedMaxCount');
        }

        // @todo Use service.
        this.reactionHelper = new ReactionsHelper();

        this.availableReactions = this.reactionHelper.getAvailableReactions();
    }

    getActionList() {
        const list = [];

        if (this.options.acl.edit && this.options.isEditable) {
            list.push({
                action: 'quickEdit',
                label: 'Edit',
                data: {
                    id: this.model.id,
                },
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_EDIT,
            });
        }

        if (this.options.acl.edit && this.options.isRemovable) {
            list.push({
                action: 'quickRemove',
                label: 'Remove',
                data: {
                    id: this.model.id,
                },
                groupIndex: 0,
                iconClass: DefaultRowActionsView.ICON_CLASS_REMOVE,
            });
        }

        if (
            this.options.isThis &&
            ['Post', 'EmailReceived', 'EmailSent'].includes(this.model.attributes.type) &&
            this.parentModel &&
            this.getAcl().checkModel(this.parentModel, 'edit') &&
            !this.isDetached
        ) {
            if (this.model.attributes.isPinned) {
                list.push({
                    action: 'unpin',
                    label: 'Unpin',
                    data: {
                        id: this.model.id,
                    },
                    groupIndex: 2,
                });
            } else if (this.pinnedMaxCount > 0) {
                list.push({
                    action: 'pin',
                    label: 'Pin',
                    data: {
                        id: this.model.id,
                    },
                    groupIndex: 2,
                    iconClass: 'fas fa-map-pin',
                });
            }
        }

        if (
            this.options.isThis &&
            this.model.attributes.type === 'Post' &&
            this.model.attributes.post &&
            !this.isDetached
        ) {
            list.push({
                action: 'quoteReply',
                label: 'Quote Reply',
                data: {
                    id: this.model.id,
                },
                groupIndex: 1,
            });
        }

        if (this.hasReactions()) {
            this.getReactionItems().forEach(item => list.push(item));
        }

        return list;
    }

    /**
     * @private
     * @return {boolean}
     */
    hasReactions() {
        return this.model.attributes.type === 'Post' &&
            this.availableReactions.length &&
            !this.options.isNotification;
    }

    async prepareRender() {
        if (!this.hasReactions() || this.availableReactions.length === 1) {
            return;
        }

        const reactionsView = new ReactionsRowActionView({
            reactions: this.availableReactions.map(type => {
                return {
                    type: type,
                    iconClass: this.reactionHelper.getIconClass(type),
                    label: this.translate(type, 'reactions'),
                    isReacted: this.isUserReacted(type),
                };
            }),
        });

        await this.assignView('reactions', reactionsView, '[data-view-key="reactions"]');
    }

    /**
     * @private
     * @param {string} type
     * @return {boolean}
     */
    isUserReacted(type) {
        /** @type {string[]} */
        const myReactions = this.model.attributes.myReactions || [];

        return myReactions.includes(type);
    }

    /**
     * @private
     * @return {module:views/record/row-actions/actions~item[]}
     */
    getReactionItems() {
        const list = [];

        if (this.availableReactions.length > 1) {
            return [{
                viewKey: 'reactions',
                groupIndex: 11,
            }];
        }

        this.availableReactions.forEach(type => {
            const iconClass = this.reactionHelper.getIconClass(type);

            const label = this.getHelper().escapeString(this.translate(type, 'reactions'));

            let html = iconClass ?
                `<span class="${iconClass} text-soft item-icon reaction-icon"></span><span class="item-text">${label}</span>` :
                label;

            const reacted = this.isUserReacted(type);

            if (reacted) {
                html =
                    `<span class="check-icon fas fa-check pull-right"></span>` +
                    `<div>${html}</div>`;
            }

            list.push({
                action: reacted ? 'unReact' : 'react',
                html: html,
                data: {
                    id: this.model.id,
                    type: type,
                },
                groupIndex: 3,
            });
        });

        return list;
    }
}

export default StreamDefaultNoteRowActionsView;
