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

import ArrayFieldView from 'views/fields/array';

export default class FieldManagerOptionsFieldView extends ArrayFieldView {

    maxItemLength = 100

    setup() {
        super.setup();

        this.translatedOptions = {};

        const list = this.model.get(this.name) || [];

        list.forEach(value => {
            this.translatedOptions[value] = this.getLanguage()
                .translateOption(value, this.options.field, this.options.scope);
        });

        this.model.fetchedAttributes.translatedOptions = this.translatedOptions;
    }

    getItemHtml(value) {
        // Do not use the `html` method to avoid XSS.

        const text = (this.translatedOptions[value] || value);

        const $div = $('<div>')
            .addClass('list-group-item link-with-role form-inline')
            .attr('data-value', value)
            .append(
                $('<div>')
                    .addClass('pull-left item-content')
                    .css('width', '92%')
                    .css('display', 'inline-block')
                    .append(
                        $('<input>')
                            .attr('type', 'text')
                            .attr('data-name', 'translatedValue')
                            .attr('data-value', value)
                            .addClass('role form-control input-sm pull-right')
                            .attr('value', text)
                            .css('width', 'auto')
                    )
                    .append(
                        $('<div>')
                            .addClass('item-text')
                            .text(value)
                    )
            )
            .append(
                $('<div>')
                    .css('width', '8%')
                    .css('display', 'inline-block')
                    .css('vertical-align', 'top')
                    .append(
                        $('<a>')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .addClass('pull-right')
                            .attr('data-value', value)
                            .attr('data-action', 'removeValue')
                            .append(
                                $('<span>').addClass('fas fa-times')
                            )
                    )
            )
            .append(
                $('<br>').css('clear', 'both')
            );

        return $div.get(0).outerHTML;
    }

    fetch() {
        const data = super.fetch();

        if (!data[this.name].length) {
            data[this.name] = null;
            data.translatedOptions = {};

            return data;
        }

        data.translatedOptions = {};

        (data[this.name] || []).forEach(value => {
            const valueInternal = CSS.escape(value);

            const translatedValue = this.$el
                .find(`input[data-name="translatedValue"][data-value="${valueInternal}"]`).val() || value;

            data.translatedOptions[value] = translatedValue.toString();
        });

        return data;
    }
}
