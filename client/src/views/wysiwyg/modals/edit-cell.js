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

import ModalView from 'views/modal';
import EditForModal from 'views/record/edit-for-modal';
import Model from 'model';
import VarcharFieldView from 'views/fields/varchar';
import ColorpickerFieldView from 'views/fields/colorpicker';
import EnumFieldView from 'views/fields/enum';

class EditCellModalView extends ModalView {

    templateContent = `
        <div class="record no-side-margin">{{{record}}}</div>
    `
    /**
     * @param {{
     *     params: {
     *         width: null|string,
     *         height: null|height,
     *         backgroundColor: null|string,
     *         verticalAlign: null|'top'|'middle'|'bottom',
     *    },
     *    onApply: function({
     *         width: null|string,
     *         height: null|height,
     *         backgroundColor: null|string,
     *         verticalAlign: null|'top'|'middle'|'bottom',
     *    }),
     * }} options
     */
    constructor(options) {
        super(options);

        this.params = options.params;
        this.onApply = options.onApply;
    }

    setup() {
        this.addButton({
            name: 'apply',
            style: 'primary',
            label: 'Apply',
            onClick: () => this.apply(),
        });

        this.addButton({
            name: 'cancel',
            label: 'Cancel',
            onClick: () => this.close(),
        });

        this.shortcutKeys = {
            'Control+Enter': () => this.apply(),
        };

        this.model = new Model({
            width: this.params.width,
            height: this.params.height,
            backgroundColor: this.params.backgroundColor,
            verticalAlign: this.params.verticalAlign,
        });

        this.recordView = new EditForModal({
            model: this.model,
            detailLayout: [
                {
                    rows: [
                        [
                            {
                                view: new VarcharFieldView({
                                    name: 'width',
                                    labelText: this.translate('width', 'wysiwygLabels'),
                                    params: {
                                        maxLength: 12,
                                    },
                                }),
                            },
                            {
                                view: new VarcharFieldView({
                                    name: 'height',
                                    labelText: this.translate('height', 'wysiwygLabels'),
                                    params: {
                                        maxLength: 12,
                                    },
                                }),
                            },
                        ],
                        [
                            {
                                view: new EnumFieldView({
                                    name: 'verticalAlign',
                                    labelText: this.translate('verticalAlign', 'wysiwygLabels'),
                                    params: {
                                        options: [
                                            '',
                                            'top',
                                            'middle',
                                            'bottom',
                                        ],
                                        translation: 'Global.wysiwygOptions.verticalAlign',
                                    },
                                }),
                            },
                            {
                                view: new ColorpickerFieldView({
                                    name: 'backgroundColor',
                                    labelText: this.translate('backgroundColor', 'wysiwygLabels'),
                                }),
                            },
                        ]
                    ],
                },
            ],
        });

        this.assignView('record', this.recordView, '.record');
    }

    apply() {
        if (this.recordView.validate()) {
            return;
        }

        let width = this.model.attributes.width;

        if (/^\d+$/.test(width)) {
            width += 'px';
        }

        let height = this.model.attributes.height;

        if (/^\d+$/.test(height)) {
            height += 'px';
        }

        this.onApply({
            width: width,
            height: height,
            backgroundColor: this.model.attributes.backgroundColor,
            verticalAlign: this.model.attributes.verticalAlign,
        });

        this.close();
    }
}

export default EditCellModalView
