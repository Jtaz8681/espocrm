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

define('crm:views/fields/ico', ['views/fields/base'], function (Dep) {

    return Dep.extend({

        // language=Handlebars
        templateContent: `{{! ~}}
            <span
                class="{{iconClass}} text-muted action icon"
                style="cursor: pointer"
                title="{{viewLabel}}"
                data-action="quickView"
                data-id="{{id}}"
                {{#if notRelationship}}data-scope="{{scope}}"{{/if}}
            ></span>
        {{~!}}`,

        data: function () {
            return {
                notRelationship: this.params.notRelationship,
                viewLabel: this.translate('View'),
                id: this.model.id,
                scope: this.model.entityType,
                iconClass: this.getMetadata().get(['clientDefs', this.model.entityType, 'iconClass']) ||
                    'far fa-calendar-times',
            };
        },
    });
});
