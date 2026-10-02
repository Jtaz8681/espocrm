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

import RowActionHandler from 'handlers/row-action';

class SendInEmailHandler extends RowActionHandler {

    isAvailable(model, action) {
        return this.view.getAcl().checkScope('Email', 'create');
    }

    process(model, action) {
        const parentModel = this.view.getParentView().model;
        const modelFactory = this.view.getModelFactory();
        const collectionFactory = this.view.getCollectionFactory();

        Espo.Ui.notifyWait();

        model.fetch()
            .then(() => {
                return new Promise(resolve => {
                    if (
                        parentModel.get('contactsIds') &&
                        parentModel.get('contactsIds').length
                    ) {
                        collectionFactory.create('Contact', contactList => {
                            const contactListFinal = [];
                            contactList.url = 'Case/' + parentModel.id + '/contacts';

                            contactList.fetch().then(() => {
                                contactList.forEach(contact => {
                                    if (contact.id === parentModel.get('contactId')) {
                                        contactListFinal.unshift(contact);
                                    } else {
                                        contactListFinal.push(contact);
                                    }
                                });

                                resolve(contactListFinal);
                            });
                        });

                        return;
                    }

                    if (parentModel.get('accountId')) {
                        modelFactory.create('Account', account => {
                            account.id = parentModel.get('accountId');

                            account.fetch()
                                .then(() => resolve([account]));
                        });

                        return;
                    }

                    if (parentModel.get('leadId')) {
                        modelFactory.create('Lead', lead => {
                            lead.id = parentModel.get('leadId');

                            lead.fetch()
                                .then(() => resolve([lead]));
                        });

                        return;
                    }

                    resolve([]);
                });
            })
            .then(list => {
                const attributes = {
                    parentType: 'Case',
                    parentId: parentModel.id,
                    parentName: parentModel.get('name'),
                    name: '[#' + parentModel.get('number') + ']',
                };

                attributes.to = '';
                attributes.cc = '';
                attributes.nameHash = {};

                list.forEach((model, i) => {
                    if (model.get('emailAddress')) {
                        if (i === 0) {
                            attributes.to += model.get('emailAddress') + ';';
                        } else {
                            attributes.cc += model.get('emailAddress') + ';';
                        }

                        attributes.nameHash[model.get('emailAddress')] = model.get('name');
                    }
                });

                Espo.loader.require('crm:knowledge-base-helper', Helper => {
                    const helper = new Helper(this.view.getLanguage());

                    helper.getAttributesForEmail(model, attributes, attributes => {
                        const viewName = this.view.getMetadata().get('clientDefs.Email.modalViews.compose') ||
                            'views/modals/compose-email';

                        this.view.createView('composeEmail', viewName, {
                            attributes: attributes,
                            selectTemplateDisabled: true,
                            signatureDisabled: true,
                        }, view => {
                            Espo.Ui.notify(false);

                            view.render();

                            this.view.listenToOnce(view, 'after:send', () => {
                                parentModel.trigger('after:relate');
                            });
                        });
                    });
                });
            })
            .catch(() => {
                Espo.Ui.notify(false);
            });
    }
}

export default SendInEmailHandler;
