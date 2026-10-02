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

define('crm:views/meeting/modals/acceptance-status', ['views/modal'], function (Dep) {

    return Dep.extend({

        backdrop: true,

        templateContent: `
            <div class="margin-bottom">
            <p>{{viewObject.message}}</p>
            </div>
            <div>
                {{#each viewObject.statusDataList}}
                <div class="margin-bottom">
                    <div>
                        <button
                            class="action btn btn-{{style}} btn-x-wide"
                            type="button"
                            data-action="setStatus"
                            data-status="{{name}}"
                        >
                        {{label}}
                        </button>
                        {{#if selected}}<span class="check-icon fas fa-check" style="vertical-align: middle; margin: 0 10px;"></span>{{/if}}
                    </div>
                </div>
                {{/each}}
            </div>
        `,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.$header = $('<span>').append(
                $('<span>').text(this.translate(this.model.entityType, 'scopeNames')),
                ' <span class="chevron-right"></span> ',
                $('<span>').text(this.model.get('name')),
                ' <span class="chevron-right"></span> ',
                $('<span>').text(this.translate('Acceptance', 'labels', 'Meeting'))
            );

            let statusList = this.getMetadata()
                .get(['entityDefs', this.model.entityType, 'fields', 'acceptanceStatus', 'options']) || [];

            this.statusDataList = [];

            statusList.filter(item => item !== 'None').forEach(item => {
                let o = {
                    name: item,
                    style: this.getMetadata()
                        .get(['entityDefs', this.model.entityType, 'fields', 'acceptanceStatus', 'style', item]) ||
                        'default',
                    label: this.getLanguage().translateOption(item, 'acceptanceStatus', this.model.entityType),
                    selected: this.model.getLinkMultipleColumn('users', 'status', this.getUser().id) === item,
                };

                this.statusDataList.push(o);
            });

            this.message = this.translate('selectAcceptanceStatus', 'messages', 'Meeting')
        },

        actionSetStatus: function (data) {
            this.trigger('set-status', data.status);
            this.close();
        },
    });
});
