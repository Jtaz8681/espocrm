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

export default class extends ModalView {

    // language=Handlebars
    templateContent = `
        <div class="panel no-side-margin">
            <table class="table table-bordered">
                {{#each typeList}}
                <tr>
                    <td style="width: 40%">
                        <a
                            class="btn btn-default btn-lg btn-full-wide"
                            href="#Admin/entityManager/formula&scope={{../scope}}&type={{this}}"
                        >
                        {{translate this category='fields' scope='EntityManager'}}
                        </a>
                    </td>
                    <td style="width: 60%">
                        <div class="complex-text">{{complexText (translate this category='messages' scope='EntityManager')}}
                    </td>
                </tr>
                {{/each}}
            </table>
        </div>
    `

    backdrop = true

    data() {
        return {
            typeList: this.typeList,
            scope: this.scope,
        };
    }

    setup() {
        this.scope = this.options.scope;

        this.typeList = [
            'beforeSaveCustomScript',
            'beforeSaveApiScript',
        ];

        this.headerText = this.translate('Formula', 'labels', 'EntityManager')
    }
}
