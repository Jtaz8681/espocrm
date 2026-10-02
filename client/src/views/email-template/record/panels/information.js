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

import SidePanelView from 'views/record/panels/side';

export default class extends SidePanelView {

    templateContent = `{{{infoText}}}`

    data() {
        const list2 = this.getMetadata().get(['clientDefs', 'EmailTemplate', 'placeholderList']) || [];

        const defs = this.getMetadata().get('app.emailTemplate.placeholders') || {};

        const list1 = Object.keys(defs)
            .sort((a, b) => {
                const o1 = defs[a].order || 0;
                const o2 = defs[b].order || 0;

                return o1 - o2;
            });

        const placeholderList = [...list1, ...list2];

        if (!placeholderList.length) {
            return {infoText: ''};
        }

        const $header = $('<h4>').text(this.translate('Available placeholders', 'labels', 'EmailTemplate') + ':');

        const $liList = placeholderList.map(item => {
            return $('<li>').append(
                $('<code>').text('{' + item + '}'),
                ' &#8211; ',
                $('<span>').text(this.translate(item, 'placeholderTexts', 'EmailTemplate'))
            )
        });

        const $ul = $('<ul>').append($liList);

        const $text = $('<span>')
            .addClass('complex-text')
            .append($header, $ul);

        return {
            infoText: $text[0].outerHTML,
        };
    }
}
