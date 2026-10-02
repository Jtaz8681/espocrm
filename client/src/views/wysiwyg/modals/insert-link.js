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

export default class WysiwygInsertLinkModal extends ModalView {

    className = 'dialog dialog-record'

    template = 'wysiwyg/modals/insert-link'

    events = {
        /** @this {WysiwygInsertLinkModal} */
        'input [data-name="url"]': function () {
            this.controlInputs();
        },
        /** @this {WysiwygInsertLinkModal} */
        'paste [data-name="url"]': function () {
            this.controlInputs();
        },
    }

    shortcutKeys = {
        /** @this {WysiwygInsertLinkModal} */
        'Control+Enter': function () {
            if (this.hasAvailableActionItem('insert')) {
                this.actionInsert();
            }
        },
    }

    data() {
        return {
            labels: this.options.labels || {},
        };
    }

    setup() {
        const labels = this.options.labels || {};

        this.headerText = labels.insert;

        this.buttonList = [
            {
                name: 'insert',
                text: this.translate('Insert'),
                style: 'primary',
                disabled: true,
            }
        ];

        this.linkInfo = this.options.linkInfo || {};

        if (this.linkInfo.url) {
            this.enableButton('insert');
        }
    }

    afterRender() {
        this.$url = this.$el.find('[data-name="url"]');
        this.$text = this.$el.find('[data-name="text"]');
        this.$openInNewWindow = this.$el.find('[data-name="openInNewWindow"]');

        const linkInfo = this.linkInfo;

        this.$url.val(linkInfo.url || '');
        this.$text.val(linkInfo.text || '');

        if ('isNewWindow' in linkInfo) {
            this.$openInNewWindow.get(0).checked = !!linkInfo.isNewWindow;
        }
    }

    controlInputs() {
        const url = this.$url.val().trim();

        if (url) {
            this.enableButton('insert');
        } else {
            this.disableButton('insert');
        }
    }

    actionInsert() {
        const url = this.$url.val().trim();
        const text = this.$text.val().trim();
        const openInNewWindow = this.$openInNewWindow.get(0).checked;

        const data = {
            url: url,
            text: text || url,
            isNewWindow: openInNewWindow,
            range: this.linkInfo.range,
        };

        this.trigger('insert', data);
        this.close();
    }
}
