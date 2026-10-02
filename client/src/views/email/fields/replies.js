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

import LinkMultipleFieldView from 'views/fields/link-multiple';

export default class extends LinkMultipleFieldView {

    getAttributeList() {
        const attributeList = super.getAttributeList();

        attributeList.push(this.name + 'Columns');

        return attributeList;
    }

    getDetailLinkHtml(id) {
        const html = super.getDetailLinkHtml(id);

        const columns = this.model.get(this.name + 'Columns') || {};

        const status = (columns[id] || {})['status'];

        return $('<div>')
            .append(
                $('<span>')
                    .addClass('fas fa-arrow-right fa-sm link-multiple-item-icon')
                    .addClass(status === 'Draft' ? 'text-warning' : 'text-success')
            )
            .append(html)
            .html();
    }
}
