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

export default class WysiwygInsertImageModal extends ModalView {

    className = 'dialog dialog-record'

    template = 'wysiwyg/modals/insert-image'

    events = {
        /** @this {WysiwygInsertImageModal} */
        'click [data-action="insert"]': function () {
            this.actionInsert();
        },
        /** @this {WysiwygInsertImageModal} */
        'input [data-name="url"]': function () {
            this.controlInsertButton();
        },
        /** @this {WysiwygInsertImageModal} */
        'paste [data-name="url"]': function () {
            this.controlInsertButton();
        },
    }

    shortcutKeys = {
        /** @this {WysiwygInsertImageModal} */
        'Control+Enter': function () {
            if (!this.$el.find('[data-name="insert"]').hasClass('disabled')) {
                this.actionInsert();
            }
        }
    }

    data() {
        return {
            labels: this.options.labels || {},
        };
    }

    setup() {
        const labels = this.options.labels || {};

        this.headerText = labels.insert;

        this.buttonList = [];
    }

    afterRender() {
        const $files = this.$el.find('[data-name="files"]');

        $files.replaceWith(
            $files.clone()
                .on('change', (e) => {
                  this.trigger('upload', e.target.files || e.target.value);
                  this.close();
                })
                .val('')
        );
    }

    controlInsertButton() {
        const value = this.$el.find('[data-name="url"]').val().trim();

        const $button = this.$el.find('[data-name="insert"]');

        if (value) {
            $button.removeClass('disabled').removeAttr('disabled');
        } else {
            $button.addClass('disabled').attr('disabled', 'disabled');
        }
    }

    actionInsert() {
        const url = this.$el.find('[data-name="url"]').val().trim();

        this.trigger('insert', url);
        this.close();
    }
}
