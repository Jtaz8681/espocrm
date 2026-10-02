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

import EmailAddressFieldView from 'views/fields/email-address';

export default class extends EmailAddressFieldView {

    setup() {
        super.setup();

        this.on('change', () => {
            const emailAddress = this.model.get('emailAddress');

            this.model.set('name', emailAddress);
        });

        const userId = this.model.get('assignedUserId');

        if (this.getUser().isAdmin() && userId !== this.getUser().id) {
            Espo.Ajax.getRequest(`User/${userId}`).then((data) => {
                const list = [];

                if (data.emailAddress) {
                    list.push(data.emailAddress);

                    this.params.options = list;

                    if (data.emailAddressData) {
                        data.emailAddressData.forEach(item => {
                            if (item.emailAddress === data.emailAddress) {
                                return;
                            }

                            list.push(item.emailAddress);
                        });
                    }

                    this.reRender();
                }
            });
        }
    }

    setupOptions() {
        if (this.model.get('assignedUserId') === this.getUser().id) {
            this.params.options = this.getUser().get('userEmailAddressList');
        }
    }
}
