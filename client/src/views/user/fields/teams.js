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

import LinkMultipleWithRoleFieldView from 'views/fields/link-multiple-with-role';

export default class extends LinkMultipleWithRoleFieldView {

    forceRoles = true

    setup() {
        super.setup();

        this.roleListMap = {};

        this.loadRoleList(() => {
            if (this.isEditMode()) {
                if (this.isRendered() || this.isBeingRendered()) {
                    this.reRender();
                }
            }
        });

        this.listenTo(this.model, 'change:teamsIds', () => {
            let toLoad = false;

            this.ids.forEach(id => {
                if (!(id in this.roleListMap)) {
                    toLoad = true;
                }
            });

            if (toLoad) {
                this.loadRoleList(() => {
                    this.reRender();
                });
            }
        });
    }

    loadRoleList(callback, context) {
        if (!this.getAcl().checkScope('Team', 'read')) {
            return;
        }

        const ids = this.ids || [];

        if (ids.length === 0) {
            return;
        }

        this.getCollectionFactory().create('Team', teams => {
            teams.maxSize = 50;
            teams.where = [
                {
                    type: 'in',
                    field: 'id',
                    value: ids,
                }
            ];

            this.listenToOnce(teams, 'sync', () => {
                teams.models.forEach(model => {
                    this.roleListMap[model.id] = model.get('positionList') || [];
                });

                callback.call(context);
            });

            teams.fetch();
        });
    }

    getDetailLinkHtml(id, name) {
        name = name || this.nameHash[id] || id;

        let role = (this.columns[id] || {})[this.columnName] || '';

        const $el = $('<div>')
            .append(
                $('<a>')
                    .attr('href', '#' + this.foreignScope + '/view/' + id)
                    .attr('data-id', id)
                    .text(name)
            );

        if (role) {
            role = this.getHelper().escapeString(role);

            $el.append(
                $('<span>').text(' '),
                $('<span>').addClass('text-muted middle-dot'),
                $('<span>').text(' '),
                $('<span>').addClass('text-muted').text(role)
            )
        }

        return $el.get(0).outerHTML;
    }

    getJQSelect(id, roleValue) {
        /** @var {string[]} */
        const roleList = Espo.Utils.clone(this.roleListMap[id] || []);

        if (!roleList.length && !roleValue) {
            return null;
        }

        roleList.unshift('');

        if (roleValue && roleList.indexOf(roleValue) === -1) {
            roleList.push(roleValue);
        }

        const $role = $('<select>')
            .addClass('role form-control input-sm pull-right')
            .attr('data-id', id);

        roleList.forEach(role => {
            const $option = $('<option>')
                .val(role)
                .text(role);

            if (role === (roleValue || '')) {
                $option.attr('selected', 'selected');
            }

            $role.append($option);
        });

        return $role;
    }
}
