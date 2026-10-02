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

import VarcharFieldView from 'views/fields/varchar';

class ColorpickerFieldView extends VarcharFieldView {

    type = 'varchar'

    detailTemplate = 'fields/colorpicker/detail'
    listTemplate = 'fields/colorpicker/detail'
    editTemplate = 'fields/colorpicker/edit'

    setup() {
        super.setup();

        this.params.maxLength = 7;

        this.wait(Espo.loader.requirePromise('lib!bootstrap-colorpicker'));
    }

    afterRender() {
        super.afterRender();

        if (this.isEditMode()) {
            const isModal = !!this.$el.closest('.modal').length;

            // noinspection JSUnresolvedReference
            this.$element.parent().colorpicker({
                format: 'hex',
                container: isModal ? this.$el : false,
                sliders: {
                    saturation: {
                        maxLeft: 200,
                        maxTop: 200,
                    },
                    hue: {
                        maxTop: 200,
                    },
                    alpha: {
                        maxTop: 200,
                    },
                },
            });

            if (isModal) {
                this.$el.find('.colorpicker')
                    .css('position', 'relative')
                    .addClass('pull-right');
            }

            this.$element.on('change', () => {
                if (this.$element.val() === '') {
                    this.$el.find('.input-group-addon > i').css('background-color', 'transparent');
                }
            });
        }
    }
}

export default ColorpickerFieldView;
