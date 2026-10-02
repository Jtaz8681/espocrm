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

import View from 'view';

class CampaignUnsubscribeView extends View {

    template = 'crm:campaign/unsubscribe'

    data() {
        return {
            isSubscribed: this.isSubscribed,
            inProcess: this.inProcess,
        };
    }

    setup() {
        super.setup();

        this.actionData = /** @type {Record} */this.options.actionData;

        this.isSubscribed = this.actionData.isSubscribed;
        this.inProcess = false;

        const endpointUrl = this.actionData.hash && this.actionData.emailAddress ?
            `Campaign/unsubscribe/${this.actionData.emailAddress}/${this.actionData.hash}`:
            `Campaign/unsubscribe/${this.actionData.queueItemId}`;

        this.addActionHandler('subscribe', () => {
            Espo.Ui.notifyWait();

            this.inProcess = true;
            this.reRender();

            Espo.Ajax.deleteRequest(endpointUrl)
                .then(() => {
                    this.isSubscribed = true;
                    this.inProcess = false;

                    this.reRender().then(() => {
                        const message = this.translate('subscribedAgain', 'messages', 'Campaign');

                        Espo.Ui.notify(message, 'success', 0, {closeButton: true});
                    });
                })
                .catch(() => {
                    this.inProcess = false;
                    this.reRender();
                });
        });

        this.addActionHandler('unsubscribe', () => {
            Espo.Ui.notifyWait();

            this.inProcess = true;
            this.reRender();

            Espo.Ajax.postRequest(endpointUrl)
                .then(() => {
                    Espo.Ui.success(this.translate('unsubscribed', 'messages', 'Campaign'), {closeButton: true});

                    this.isSubscribed = false;
                    this.inProcess = false;

                    this.reRender().then(() => {
                        const message = this.translate('unsubscribed', 'messages', 'Campaign');

                        Espo.Ui.notify(message, 'success', 0, {closeButton: true});
                    });
                })
                .catch(() => {
                    this.inProcess = false;
                    this.reRender();
                });
        });
    }
}

export default CampaignUnsubscribeView;
