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

import ModalView from 'views/modal';

export default class extends ModalView {

    cssName = 'test-send'

    templateContent = `
        <label class="control-label">{{translate \'Email Address\' scope=\'Email\'}}</label>
        <input type="text" name="emailAddress" value="{{emailAddress}}" class="form-control">
    `

    data() {
        return {
            emailAddress: this.options.emailAddress,
        };
    }

    setup() {
        this.buttonList = [
            {
                name: 'send',
                text: this.translate('Send', 'labels', 'Email'),
                style: 'primary',
                onClick: () => {
                    const emailAddress = this.$el.find('input').val();

                    if (emailAddress === '') {
                        return;
                    }

                    this.trigger('send', emailAddress);
                },
            },
            {
                name: 'cancel',
                label: 'Cancel',
                onClick: dialog =>{
                    dialog.close();
                },
            }
        ];
    }
}
