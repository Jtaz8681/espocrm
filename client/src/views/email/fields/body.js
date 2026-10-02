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

import WysiwygFieldView from 'views/fields/wysiwyg';

class EmailBodyFieldView extends WysiwygFieldView {

    useIframe = true
    hasBodyPlainField = true
    toShowQuotePart = false;

    /**
     * @type {string}
     * @private
     */
    replyPart

    getAttributeList() {
        return ['body', 'bodyPlain'];
    }

    setupToolbar() {
        super.setupToolbar();

        const attachmentItem = this.toolbar.find(it => it[0] === 'attachment');

        if (attachmentItem) {
            attachmentItem[1].push('insert-field');
        } else {
            this.toolbar.push(['insert-field', ['insert-field']]);
        }


        this.buttons['insert-field'] = () => {
            const ui = $.summernote.ui;

            const button = ui.button({
                contents: '<i class="fas fa-plus"></i>',
                tooltip: this.translate('Insert Field', 'labels', 'Email'),
                click: () => {
                    this.showInsertFieldModal();
                },
            });

            return button.render();
        };

        this.listenTo(this.model, 'change', m => {
            if (!this.isRendered()) {
                return;
            }

            if (m.hasChanged('parentId') || m.hasChanged('to')) {
                this.controlInsertFieldButton();
            }
        });
    }

    onEditModeSet() {
        this.toShowQuotePart = false;

        return super.onEditModeSet();
    }

    afterRender() {
        super.afterRender();

        this.controlInsertFieldButton();

        if (this.isReadMode() && this.replyPart) {
            this.element.appendChild(this.createShowQuotePartButton());
        }
    }

    controlInsertFieldButton() {
        const $b = this.$el.find('.note-insert-field > button');

        if (
            this.model.get('to') &&
            this.model.get('to').length ||
            this.model.get('parentId')
        ) {
            $b.removeAttr('disabled').removeClass('disabled');
        } else {
            $b.attr('disabled', 'disabled').addClass('disabled');
        }
    }

    showInsertFieldModal() {
        let to = this.model.get('to');

        if (to) {
            to = to.split(';')[0].trim();
        }

        const parentId = this.model.get('parentId');
        const parentType = this.model.get('parentType');

        Espo.Ui.notifyWait();

        this.createView('insertFieldDialog', 'views/email/modals/insert-field', {
            parentId: parentId,
            parentType: parentType,
            to: to,
        }, view => {
            view.render();

            Espo.Ui.notify();

            this.listenToOnce(view, 'insert', /** string */string => {
                if (this.$summernote) {
                    if (string.includes('\n')) {
                        string = string.replace(/\r\n|\r|\n/g, '<br>');
                        const html = '<p>' + string + '</p>';

                        this.$summernote.summernote('editor.pasteHTML', html);
                    } else {
                        this.$summernote.summernote('editor.insertText', string);
                    }
                }

                this.clearView('insertFieldDialog');
            });
        });
    }

    getValueForIframe() {
        let contents = super.getValueForIframe();

        if (this.toShowQuotePart) {
            this.replyPart = undefined;

            return contents;
        }

        this.replyPart = undefined;

        contents = this.processQuotePart(contents);

        if (!this.replyPart) {
            return contents;
        }

        return contents
    }

    getValueForEdit() {
        this.replyPart = undefined;
        const contents = super.getValueForEdit();

        return this.processQuotePart(contents, true);
    }

    /**
     * @private
     * @param {string} contents
     * @param {boolean} [isEdit=false]
     * @return {string}
     */
    processQuotePart(contents, isEdit) {
        const container = document.createElement('div');
        container.innerHTML = contents;

        let selector = `:scope > [data-quote-start="true"]`;

        if (!isEdit) {
            selector +=
                ', :scope > blockquote:last-child' +
                ', :scope > div:last-child > blockquote:last-child';
        }

        let element = container.querySelector(selector);

        if (!element) {
            return contents;
        }

        container.querySelectorAll('style, link[ref="stylesheet"]').forEach(element => {
            element.parentElement.removeChild(element);
        });

        this.replyPart = '';
        /** @type {HTMLElement[]} */
        const removeList = [];

        while (element) {
            this.replyPart += element.outerHTML;

            removeList.push(element);
            element = element.nextElementSibling;
        }

        removeList.forEach(element => element.parentElement.removeChild(element));

        if (isEdit) {
            this.element.appendChild(this.createShowQuotePartButton());
        }

        return container.innerHTML;
    }

    /**
     * @private
     * @return {HTMLAnchorElement}
     */
    createShowQuotePartButton() {
        const a = this.showQuoteButtonElement = document.createElement('a');

        a.setAttribute('role', 'button');
        a.innerHTML = '...';
        a.classList.add('show-quote-button', 'btn', 'btn-default', 'btn-sm');
        a.addEventListener('click', () => this.showQuotePart());

        return a;
    }

    /** @private */
    showQuotePart() {
        if (this.isReadMode()) {
            this.toShowQuotePart = true;
            this.reRender();

            return;
        }

        const value = this.$summernote.summernote('code') + this.replyPart;

        this.replyPart = undefined;
        this.showQuoteButtonElement.parentElement.removeChild(this.showQuoteButtonElement);

        this.$summernote.summernote('code', value);
    }

    fetch() {
        const data = super.fetch();

        if (this.model.attributes.isHtml && this.replyPart) {
            if (data[this.name] == null) {
                data[this.name] = '';
            }

            data[this.name] += this.replyPart;
        }

        if (this.model.attributes.isHtml) {
            data[this.name + 'Plain'] = this.htmlToPlain(data[this.name]);
        }

        return data;
    }
}

export default EmailBodyFieldView;
