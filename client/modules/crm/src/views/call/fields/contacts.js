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

import AttendeesFieldView from 'modules/crm/views/meeting/fields/attendees';

export default class extends AttendeesFieldView {

    getAttributeList() {
        return [
            ...super.getAttributeList(),
            'phoneNumbersMap',
        ]
    }

    getDetailLinkHtml(id, name) {
        const html = super.getDetailLinkHtml(id, name);

        const key = this.foreignScope + '_' + id;
        const phoneNumbersMap = this.model.get('phoneNumbersMap') || {};

        if (!(key in phoneNumbersMap)) {
            return html;
        }

        const number = phoneNumbersMap[key];

        const $item = $(html);

        // @todo Format phone number.

        $item
            .append(
                ' ',
                $('<span>').addClass('text-muted middle-dot'),
                ' ',
                $('<a>')
                    .attr('href', 'tel:' + number)
                    .attr('data-phone-number', number)
                    .attr('data-action', 'dial')
                    .addClass('small')
                    .text(number)
            )

        return $('<div>')
            .append($item)
            .get(0).outerHTML;
    }
}
