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

import TextFieldView from 'views/fields/text';

export default class StreamPostFieldView extends TextFieldView {

    data() {
        const data = super.data();

        if (this.isDetailMode() || this.isListMode()) {
            data.htmlValue = this.getTransformedValue();
        }

        return data;
    }

    /**
     * @private
     * @return {Handlebars.SafeString|string}
     */
    getTransformedValue() {
        let text = super.getValueForDisplay();

        if (typeof text !== 'string' && !(text instanceof String)) {
            return '';
        }

        /** @type {Record} */
        const data = this.model.attributes.data || {}

        const mentionData = /** @type {Record.<string, {id: string, name: string}>} */
            data.mentions || {};

        const items = Object.keys(mentionData).sort((a, b) => b.length - a.length);

        if (!items.length) {
            return this.getHelper().transformMarkdownText(text);
        }

        items.forEach(item => {
            const name = mentionData[item].name;
            const id = mentionData[item].id;

            const part = `[${name}](#User/view/${id})`;

            text = text.replace(new RegExp(item, 'g'), part);
        });

        let html = this.getHelper().transformMarkdownText(text).toString();

        const body = new DOMParser().parseFromString(html, 'text/html').body;

        items.forEach(item => {
            const id = mentionData[item].id;
            const url = `#User/view/${id}`;

            const avatarHtml = this.getHelper().getAvatarHtml(id, 'small', 16, 'avatar-link');

            if (!avatarHtml) {
                return;
            }

            const img = new DOMParser().parseFromString(avatarHtml, 'text/html').body.childNodes[0];

            body.querySelectorAll(`a[href="${url}"]`).forEach(a => {
                if (id === this.getUser().id) {
                    a.classList.add('text-warning');
                } else {
                    a.classList.add('text-default');
                }

                const span = document.createElement('span');
                span.classList.add('nowrap', 'name-avatar');

                span.append(img.cloneNode());

                a.parentNode.replaceChild(span, a);

                span.append(a);
            });
        });

        html = body.innerHTML;

        return html;
    }
}
