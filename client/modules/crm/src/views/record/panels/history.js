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

import ActivitiesPanelView from 'crm:views/record/panels/activities';
import EmailHelper from 'email-helper';
import RecordModalHelper from 'helpers/record-modal';

class HistoryPanelView extends ActivitiesPanelView {

    name = 'history'
    orderBy = 'dateStart'
    orderDirection = 'desc'
    rowActionsView = 'crm:views/record/row-actions/history'

    actionList = []

    listLayout = {
        'Email': {
            rows: [
                [
                    {name: 'ico', view: 'crm:views/fields/ico'},
                    {
                        name: 'name',
                        link: true,
                    },
                ],
                [
                    {
                        name: 'dateSent',
                        soft: true
                    },
                    {
                        name: 'from',
                    },
                    {
                        name: 'hasAttachment',
                        view: 'views/email/fields/has-attachment'
                    },
                ],
            ]
        },
    }

    where = {
        scope: false,
    }

    setupActionList() {
        super.setupActionList();

        this.actionList.push({
            action: 'archiveEmail',
            label: 'Archive Email',
            acl: 'create',
            aclScope: 'Email',
            iconClass: 'far fa-envelope',
        });
    }

    getArchiveEmailAttributes(scope, data, callback) {
        const attributes = {
            dateSent: this.getDateTime().getNow(15),
            status: 'Archived',
            from: this.model.get('emailAddress'),
            to: this.getUser().get('emailAddress'),
        };

        if (this.model.entityType === 'Contact') {
            if (this.getConfig().get('b2cMode')) {
                attributes.parentType = 'Contact';
                attributes.parentName = this.model.get('name');
                attributes.parentId = this.model.id;
            } else {
                if (this.model.get('accountId')) {
                    attributes.parentType = 'Account';
                    attributes.parentId = this.model.get('accountId');
                    attributes.parentName = this.model.get('accountName');
                }
            }
        } else if (this.model.entityType === 'Lead') {
            attributes.parentType = 'Lead';
            attributes.parentId = this.model.id
            attributes.parentName = this.model.get('name');
        }

        attributes.nameHash = {};
        attributes.nameHash[this.model.get('emailAddress')] = this.model.get('name');

        if (scope) {
            if (!attributes.parentId) {
                if (this.checkParentTypeAvailability(scope, this.model.entityType)) {
                    attributes.parentType = this.model.entityType;
                    attributes.parentId = this.model.id;
                    attributes.parentName = this.model.get('name');
                }
            } else {
                if (attributes.parentType && !this.checkParentTypeAvailability(scope, attributes.parentType)) {
                    attributes.parentType = null;
                    attributes.parentId = null;
                    attributes.parentName = null;
                }
            }
        }

        callback.call(this, attributes);
    }

    // noinspection JSUnusedGlobalSymbols
    actionArchiveEmail(data) {
        const scope = 'Email';

        let relate = null;

        if (this.model.hasLink('emails')) {
            relate = {
                model: this.model,
                link: this.model.getLinkParam('emails', 'foreign'),
            };
        }

        this.getArchiveEmailAttributes(scope, data, attributes => {
            const helper = new RecordModalHelper();

            helper.showCreate(this, {
                entityType: 'Email',
                attributes: attributes,
                relate: relate,
                afterSave: () => {
                    this.collection.fetch();

                    this.model.trigger('after:relate');
                },
            });
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionReply(data) {
        const id = data.id;

        if (!id) {
            return;
        }

        const emailHelper = new EmailHelper();

        Espo.Ui.notifyWait();

        this.getModelFactory().create('Email')
            .then(model => {
                model.id = id;

                model.fetch()
                    .then(() => {
                        const replyToAllByDefault = this.getPreferences().get('emailReplyToAllByDefault');

                        const attributes = emailHelper.getReplyAttributes(model, replyToAllByDefault);

                        const viewName = this.getMetadata().get('clientDefs.Email.modalViews.compose') ||
                            'views/modals/compose-email';

                        return this.createView('quickCreate', viewName, {
                            attributes: attributes,
                            focusForCreate: true,
                        });
                    })
                    .then(view => {
                        view.render();

                        this.listenToOnce(view, 'after:save', () => {
                            this.collection.fetch();
                            this.model.trigger('after:relate');
                        });

                        Espo.Ui.notify(false);
                    });
            });
    }
}

export default HistoryPanelView;
