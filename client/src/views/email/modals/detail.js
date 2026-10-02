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

import DetailModalView from 'views/modals/detail';
import EmailHelper from 'email-helper';
import MultiCollection from 'multi-collection';
import Ui from 'ui';

export default class EmailDetailModalView extends DetailModalView {

    setup() {
        super.setup();

        this.addButton({
            name: 'reply',
            label: 'Reply',
            hidden: this.model && this.model.get('status') === 'Draft',
            style: 'danger',
            position: 'right',
        }, true)

        if (this.model) {
            this.listenToOnce(this.model, 'sync', () => {
                setTimeout(() => {
                    this.model.set('isRead', true);
                }, 50);
            });
        }
    }

    controlRecordButtonsVisibility() {
        super.controlRecordButtonsVisibility();

        if (this.model.get('status') === 'Draft' || !this.getAcl().check('Email', 'create')) {
            this.hideActionItem('reply');

            return;
        }

        this.showActionItem('reply');
    }

    // noinspection JSUnusedGlobalSymbols
    async actionReply() {
        const replyByDefault = this.getPreferences().get('emailReplyToAllByDefault');

        const emailHelper = new EmailHelper();

        const attributes = emailHelper.getReplyAttributes(this.model, replyByDefault);

        Ui.notifyWait();

        const viewName = this.getMetadata().get('clientDefs.Email.modalViews.compose') ||
            'views/modals/compose-email';

        const view = await this.createView('quickCreate', viewName, {
            attributes: attributes,
            focusForCreate: true,
        });

        this.listenTo(view, 'after:save', () => {
            this.model.fetch();
        });

        await view.render();

        Ui.notify();

        this.listenToOnce(view, 'after:send', () => {
            if (!(this.sourceModel?.collection instanceof MultiCollection)) {
                return;
            }

            // Fetch history.
            this.sourceModel.collection.fetch();
        });
    }
}
