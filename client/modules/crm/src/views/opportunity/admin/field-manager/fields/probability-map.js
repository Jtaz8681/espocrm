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

import BaseFieldView from 'views/fields/base';
import $ from 'jquery';

// noinspection JSUnusedGlobalSymbols
export default class extends BaseFieldView {

    editTemplateContent = `
        <div class="list-group link-container no-input">
            {{#each stageList}}
                <div class="list-group-item form-inline">
                    <div style="display: inline-block; width: 100%;">
                        <input
                            class="role form-control input-sm pull-right"
                            data-name="{{./this}}" value="{{prop ../values this}}"
                        >
                        <div>{{./this}}</div>
                    </div>
                    <br class="clear: both;">
                </div>
            {{/each}}
        </div>
    `

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:options', function (m, v, o) {
            const probabilityMap = this.model.get('probabilityMap') || {};

            if (o.ui) {
                (this.model.get('options') || []).forEach(item => {
                    if (!(item in probabilityMap)) {
                        probabilityMap[item] = 50;
                    }
                });

                this.model.set('probabilityMap', probabilityMap);
            }

            this.reRender();
        });
    }

    data() {
        const data = {};

        const values = this.model.get('probabilityMap') || {};

        data.stageList = this.model.get('options') || [];
        data.values = values;

        return data;
    }

    fetch() {
        const data = {
            probabilityMap: {},
        };

        (this.model.get('options') || []).forEach(item => {
            data.probabilityMap[item] = parseInt($(this.element).find(`input[data-name="${item}"]`).val());
        });

        return data;
    }

    afterRender() {
        $(this.element).find('input').on('change', () => {
            this.trigger('change')
        });
    }
}
