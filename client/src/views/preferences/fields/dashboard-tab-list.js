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

// noinspection JSUnusedGlobalSymbols
export default class extends ArrayFieldView {

    maxItemLength = 36

    setup() {
        super.setup();

        this.translatedOptions = {};

        const list = this.model.get(this.name) || [];

        list.forEach(value => {
            this.translatedOptions[value] = value;
        });

        this.validations.push('uniqueLabel');
    }

    getItemHtml(value) {
        value = value.toString();

        const translatedValue = this.translatedOptions[value] || value;

        return $('<div>')
            .addClass('list-group-item link-with-role form-inline')
            .attr('data-value', value)
            .append(
                (() => {
                    const span = document.createElement('span');
                    span.className = 'drag-handle pull-left';
                    span.append(
                        (() => {
                            const span = document.createElement('span');
                            span.className = 'fas fa-grip fa-sm';

                            return span;
                        })(),
                    );

                    return span;
                })(),
            )
            .append(
                $('<div>')
                    .addClass('pull-left')
                    .css('width', 'calc(100% - var(--36px))')
                    .css('display', 'inline-block')
                    .append(
                        $('<input>')
                            .attr('maxLength', this.maxItemLength)
                            .attr('data-name', 'translatedValue')
                            .attr('data-value', value)
                            .addClass('role form-control input-sm')
                            .attr('value', translatedValue)
                            .css('width', 'calc(100% - var(--4px))')
                    )
            )
            .append(
                $('<div>')
                    .css('width', 'var(--18px)')
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
            .get(0).outerHTML;
    }

    /**
     * @private
     * @return {boolean}
     */
    validateUniqueLabel() {
        const keyList = this.model.get(this.name) || [];
        const labels = this.model.get('translatedOptions') || {};
        const metLabelList = [];

        for (const key of keyList) {
            const label = labels[key];

            if (!label) {
                return true;
            }

            if (metLabelList.indexOf(label) !== -1) {
                return true;
            }

            metLabelList.push(label);
        }

        return false;
    }

    fetch() {
        const data = super.fetch();

        data.translatedOptions = {};

        (data[this.name] || []).forEach(value => {
            const valueInternal = CSS.escape(value);

            data.translatedOptions[value] = this.$el
                .find(`input[data-name="translatedValue"][data-value="${valueInternal}"]`)
                .val() || value;
        });

        return data;
    }
}
