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

class ArrayIntFieldView extends ArrayFieldView {

    type = 'arrayInt'

    fetchFromDom() {
        let selected = [];

        this.$el.find('.list-group .list-group-item').each((i, el) => {
            let value = $(el).data('value');

            if (typeof value === 'string' || value instanceof String) {
                value = parseInt($(el).data('value'));
            }

            selected.push(value);
        });

        this.selected = selected;
    }

    addValue(value) {
        value = parseInt(value);

        if (isNaN(value)) {
            return;
        }

        super.addValue(value);
    }

    removeValue(value) {
        value = parseInt(value);

        if (isNaN(value)) {
            return;
        }

        const valueInternal = CSS.escape(value.toString());

        this.$list.children('[data-value="' + valueInternal + '"]').remove();

        const index = this.selected.indexOf(value);

        this.selected.splice(index, 1);
        this.trigger('change');
    }
}

export default ArrayIntFieldView;
