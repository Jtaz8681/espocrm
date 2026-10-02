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

import ListView from 'views/list';

class PortalUserListView extends ListView {

    defaultOrderBy = 'createdAt'
    defaultOrder = 'desc'

    async actionCreate(data) {
        /**
         * @type {
         *     module:views/modals/select-records~Options &
         *     {onSkip: function()}
         * }
         */
        const options = {
            entityType: 'Contact',
            primaryFilterName: 'notPortalUsers',
            createButton: false,
            mandatorySelectAttributeList: [
                'salutationName',
                'firstName',
                'lastName',
                'accountName',
                'accountId',
                'emailAddress',
                'emailAddressData',
                'phoneNumber',
                'phoneNumberData',
            ],
            onSelect: models => {
                const model = models[0];

                const attributes = {};

                attributes.contactId = model.id;
                attributes.contactName = model.attributes.name;

                if (model.attributes.accountId) {
                    const names = {};
                    names[model.attributes.accountId] = model.attributes.accountName;

                    attributes.accountsIds = [model.attributes.accountId];
                    attributes.accountsNames = names;
                }

                attributes.firstName = model.get('firstName');
                attributes.lastName = model.get('lastName');
                attributes.salutationName = model.get('salutationName');

                attributes.emailAddress = model.get('emailAddress');
                attributes.emailAddressData = model.get('emailAddressData');

                attributes.phoneNumber = model.get('phoneNumber');
                attributes.phoneNumberData = model.get('phoneNumberData');

                attributes.userName = attributes.emailAddress;

                if (attributes.userName) {
                    attributes.userName = attributes.userName.toLowerCase();
                }

                attributes.type = 'portal';

                const url = `#${this.scope}/create`;

                this.getRouter().dispatch(this.scope, 'create', {attributes: attributes});
                this.getRouter().navigate(url, {trigger: false});
            },
            onSkip: () => {
                const attributes = {
                    type: 'portal',
                };

                const url = `#${this.scope}/create`;

                this.getRouter().dispatch(this.scope, 'create', {attributes: attributes});
                this.getRouter().navigate(url, {trigger: false});
            },
        };

        // As the file is supposed to bundled separately, resort to async module loading.
        /** @type {typeof import('modules/crm/views/contact/modals/select-for-portal-user').default} */
        const SelectForPortalUserModalView =
            await Espo.loader.requirePromise('modules/crm/views/contact/modals/select-for-portal-user');

        const view = new SelectForPortalUserModalView(options);

        await this.assignView('modal', view);

        await view.render();
    }
}

export default PortalUserListView;
