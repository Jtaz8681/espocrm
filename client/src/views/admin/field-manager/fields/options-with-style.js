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

import OptionsView from 'views/admin/field-manager/fields/options';

export default class extends OptionsView {

    noDragHandle = true

    setup() {
        super.setup();

        this.optionsStyleMap = this.model.get('style') || {};

        this.styleList = [
            'default',
            'success',
            'danger',
            'warning',
            'info',
            'primary',
        ];

        this.addActionHandler('selectOptionItemStyle', (e, target) => {
            const style = target.dataset.style;
            const value = target.dataset.value;

            this.changeStyle(value, style);
        })
    }

    changeStyle(value, style) {
        const val = CSS.escape(value);

        this.$el
            .find(`[data-action="selectOptionItemStyle"][data-value="${val}"] .check-icon`)
            .addClass('hidden');

        this.$el
            .find(`[data-action="selectOptionItemStyle"][data-value="${val}"][data-style="${style}"] .check-icon`)
            .removeClass('hidden');

        const $item = this.$el.find(`.list-group-item[data-value="${val}"]`).find('.item-text');

        this.styleList.forEach(item => {
            $item.removeClass('text-' + item);
        });

        $item.addClass('text-' + style);

        if (style === 'default') {
            style = null;
        }

        this.optionsStyleMap[value] = style;
    }

    getItemHtml(value) {
        // Do not use the `html` method to avoid XSS.

        const html = super.getItemHtml(value);

        const styleList = this.styleList;
        const styleMap = this.optionsStyleMap;

        let style = 'default';
        const $liList = [];

        styleList.forEach(item => {
            let isHidden = true;

            if (styleMap[value] === item) {
                style = item;
                isHidden = false;
            }
            else {
                if (item === 'default' && !styleMap[value]) {
                    isHidden = false;
                }
            }

            const text = this.getLanguage().translateOption(item, 'style', 'LayoutManager');

            const $li = $('<li>')
                .append(
                    $('<a>')
                        .attr('role', 'button')
                        .attr('tabindex', '0')
                        .attr('data-action', 'selectOptionItemStyle')
                        .attr('data-style', item)
                        .attr('data-value', value)
                        .append(
                            $('<span>')
                                .addClass('check-icon fas fa-check pull-right')
                                .addClass(isHidden ? 'hidden' : ''),
                            $('<div>')
                                .addClass(`text-${item}`)
                                .text(text)
                        )
                );

            $liList.push($li);
        });

        const $dropdown = $('<div>')
            .addClass('btn-group pull-right')
            .append(
                $('<button>')
                    .addClass('btn btn-link btn-sm dropdown-toggle')
                    .attr('type', 'button')
                    .attr('data-toggle', 'dropdown')
                    .append(
                        $('<span>').addClass('caret')
                    ),
                $('<ul>')
                    .addClass('dropdown-menu pull-right')
                    .append($liList)
            );

        const $item = $(html);

        $item.find('.item-content > input').after($dropdown);
        $item.find('.item-text').addClass(`text-${style}`);
        $item.addClass('link-group-item-with-columns');

        return $item.get(0).outerHTML;
    }

    fetch() {
        const data = super.fetch();

        data.style = {};

        (data.options || []).forEach(item => {
            const style = this.optionsStyleMap[item];

            if (style == null) {
                return;
            }

            data.style[item] = style;
        });

        return data;
    }
}
