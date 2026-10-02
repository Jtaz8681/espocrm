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

export default class FieldAttributesFieldView extends BaseFieldView {

    // language=Handlebars
    detailTemplateContent = `
        {{#if dataList.length}}
            <table class="table table-bordered" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th
                        >{{translate 'name' category='fields' scope='Admin'}}</th>
                        <th style="width: 33%"
                        >{{translate 'type' category='fields' scope='Admin'}}</th>
                        <th style="width: 16%"
                        >{{translate 'notStorable' category='fields' scope='Admin'}}</th>
                        <th style="width: 16%"
                        >{{translate 'readOnly' category='fields' scope='Admin'}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each dataList}}
                        <tr>
                            <td>{{name}}</td>
                            <td>{{type}}</td>
                            <td>
                                <input
                                    type="checkbox"
                                    class="form-checkbox form-checkbox-simple"
                                    disabled
                                    {{#if notStorable}} checked {{/if}}
                                >
                            </td>
                            <td>
                                <input
                                    type="checkbox"
                                    class="form-checkbox form-checkbox-simple"
                                    disabled
                                    {{#if readOnly}} checked {{/if}}
                                >
                            </td>
                        </tr>
                    {{/each}}
                </tbody>
            </table>
        {{else}}
            <span class="none-value">{{translate 'None'}}</span>
        {{/if}}
    `

    data() {
        return {
            dataList: this.model.attributes.attributes || [],
        };
    }
}
