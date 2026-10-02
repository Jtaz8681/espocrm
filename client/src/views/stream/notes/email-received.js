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
import EmailBodyFieldView from 'views/email/fields/body';
import AttachmentMultipleFieldView from 'views/fields/attachment-multiple';

class EmailReceivedNoteStreamView extends NoteStreamView {

    template = 'stream/notes/email-received'

    /**
     * @protected
     * @type {boolean}
     */
    isRemovable = false

    /**
     * @protected
     * @type {boolean}
     */
    isSystemAvatar = true

    /**
     * @private
     * @type {boolean}
     */
    detailsIsShown = false

    /**
     * @private
     * @type {import('views/fields/base').default}
     */
    bodyFieldView

    /**
     * @private
     * @type {import('views/fields/attachment-multiple').default}
     */
    attachmentsFieldView

    /**
     * @private
     * @type {import('model').default}
     */
    formModel

    /**
     * @private
     * @type {string}
     */
    emailId

    /**
     * @private
     * @type {boolean}
     */
    emailNotLoaded = false

    data() {
        return {
            ...super.data(),
            emailId: this.emailId,
            emailName: this.emailName,
            hasPost: this.hasPost && (!this.detailsIsShown || !this.bodyFieldView),
            mutedPost: this.hasPost && this.detailsIsShown && !this.bodyFieldView && !this.emailNotLoaded,
            hasAttachments: this.hasAttachments,
            emailIconClassName: this.getMetadata().get(['clientDefs', 'Email', 'iconClass']) || '',
            isPinned: this.isThis && this.model.get('isPinned') && this.model.collection &&
                !this.model.collection.pinnedList,
            detailsIsShown: this.detailsIsShown,
            hasExpand: !this.options.isNotification,
        };
    }

    setup() {
        this.addActionHandler('expandDetails', () => this.toggleDetails());

        const data =
            /**
             * @type {{
             *      emailId: string,
             *      emailName: string,
             *      personEntityType?: string,
             *      personEntityId?: string,
             *      personEntityName?: string,
             *      isInitial?: boolean,
             * }} */
            this.model.get('data') || {};

        this.emailId = data.emailId;
        this.emailName = data.emailName;

        if (
            this.parentModel &&
            (
                this.model.attributes.parentType === this.parentModel.entityType &&
                this.model.attributes.parentId === this.parentModel.id
            )
        ) {
            if (this.model.attributes.post) {
                this.createField('post', null, null, 'views/stream/fields/post');
                this.hasPost = true;
            }

            if ((this.model.attributes.attachmentsIds || []).length) {
                this.createField(
                    'attachments',
                    'attachmentMultiple',
                    {},
                    'views/stream/fields/attachment-multiple',
                    {
                        previewSize: this.options.isNotification || this.options.isUserStream ?
                            'small' : 'medium',
                    }
                );

                this.hasAttachments = true;
            }
        }

        this.messageData['email'] =
            $('<a>')
                .attr('href', `#Email/view/${data.emailId}`)
                .text(data.emailName)
                .attr('data-scope', 'Email')
                .attr('data-id', data.emailId);

        this.setupEmailMessage(data);

        if (this.isThis) {
            this.messageName += 'This';
        }

        this.createMessage();
    }

    /**
     * @return {import('views/fields/text').default}
     */
    getPostView() {
        return this.getView('post');
    }

    /**
     * @protected
     * @param {Record} data
     */
    setupEmailMessage(data) {
        this.messageName = 'emailReceived';

        if (data.isInitial) {
            this.messageName += 'Initial';
        }

        if (data.personEntityId) {
            this.messageName += 'From';

            this.messageData['from'] =
                $('<a>')
                    .attr('href', `#${data.personEntityType}/view/${data.personEntityId}`)
                    .text(data.personEntityName)
                    .attr('data-scope', data.personEntityType)
                    .attr('data-id', data.personEntityId);
        }

        if (
            this.model.attributes.parentType === data.personEntityType &&
            this.model.attributes.parentId === data.personEntityId
        ) {
            this.isThis = true;
        }
    }

    /**
     * @private
     */
    async toggleDetails() {
        this.detailsIsShown = !this.detailsIsShown;

        if (!this.detailsIsShown && this.formModel) {
            this.formModel.abortLastFetch();

            Espo.Ui.notify();
        }

        const postView = this.getPostView();

        await this.reRender();

        if (!this.detailsIsShown || !this.emailId) {
            return;
        }

        if (postView) {
            postView.seeMoreText = false;
        }

        if (this.bodyFieldView) {
            this.bodyFieldView.toShowQuotePart = false;

            await this.bodyFieldView.reRender();

            return;
        }

        this.formModel = await this.getModelFactory().create('Email');

        this.formModel.id = this.emailId;

        Espo.Ui.notifyWait();

        try {
            await this.formModel.fetch();
        } catch (e) {
            this.emailNotLoaded = true;

            await this.reRender();

            return;
        }

        this.bodyFieldView = new EmailBodyFieldView({
            name: 'body',
            model: this.formModel,
            mode: 'detail',
            readOnly: true,
        });

        await this.assignView('bodyField', this.bodyFieldView, '[data-name="body"]');

        if (
            !this.hasAttachments &&
            this.formModel.attributes.attachmentsIds &&
            this.formModel.attributes.attachmentsIds.length
        ) {
            this.attachmentsFieldView = new AttachmentMultipleFieldView({
                name: 'attachments',
                model: this.formModel,
                mode: 'detail',
                readOnly: true,
            });

            await this.assignView('attachmentsField', this.attachmentsFieldView, '[data-name="attachments"]');
        }

        Espo.Ui.notify();

        const minHeight = postView && postView.element ? postView.element.offsetHeight : null;

        await this.reRender();

        if (minHeight) {
            const bodyContainer = this.bodyFieldView.element;

            if (bodyContainer) {
                bodyContainer.style.minHeight = minHeight + 'px';

                setTimeout(() => bodyContainer.style.minHeight = '', 200);
            }
        }
    }
}

export default EmailReceivedNoteStreamView;
