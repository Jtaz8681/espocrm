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

import LinkFieldView from 'views/fields/link';
import Autocomplete from 'ui/autocomplete';

class UserFieldView extends LinkFieldView {

    searchTemplate = 'fields/user/search'

    setup() {
        super.setup();

        // noinspection JSUnresolvedReference
        if (this.options.isInComplexField) {
            this.linkClass = 'text-default';
        }
    }

    setupSearch() {
        super.setupSearch();

        this.searchTypeList = Espo.Utils.clone(this.searchTypeList);
        this.searchTypeList.push('isFromTeams');

        this.searchData.teamIdList = this.getSearchParamsData().teamIdList ||
            this.searchParams.teamIdList || [];
        this.searchData.teamNameHash = this.getSearchParamsData().teamNameHash ||
            this.searchParams.teamNameHash || {};

        this.events['click a[data-action="clearLinkTeams"]'] = e => {
            const id = $(e.currentTarget).data('id').toString();

            this.deleteLinkTeams(id);
        };

        this.addActionHandler('selectLinkTeams', () => this.actionSelectLinkTeams());

        this.events['click a[data-action="clearLinkTeams"]'] = e => {
            const id = $(e.currentTarget).data('id').toString();

            this.deleteLinkTeams(id);
        };
    }

    getSelectPrimaryFilterName() {
        return 'active';
    }

    /**
     * @protected
     */
    async actionSelectLinkTeams() {
        const viewName = this.getMetadata().get('clientDefs.Team.modalViews.select') ||
            'views/modals/select-records';

        /** @type {module:views/modals/select-records~Options} */
        const options = {
            entityType: 'Team',
            createButton: false,
            multiple: true,
            onSelect: models => {
                models.forEach(model => this.addLinkTeams(model.id, model.attributes.name));
            },
        };

        Espo.Ui.notifyWait();

        const view = await this.createView('modal', viewName, options);

        await view.render();
    }

    handleSearchType(type) {
        super.handleSearchType(type);

        if (type === 'isFromTeams') {
            this.$el.find('div.teams-container').removeClass('hidden');
        } else {
            this.$el.find('div.teams-container').addClass('hidden');
        }
    }

    afterRender() {
        super.afterRender();

        if (this.mode === this.MODE_SEARCH) {
            const $elementTeams = this.$el.find('input.element-teams');

            /** @type {module:ajax.AjaxPromise & Promise<any>} */
            let lastAjaxPromise;

            const autocomplete = new Autocomplete($elementTeams.get(0), {
                minChars: 1,
                focusOnSelect: true,
                handleFocusMode: 3,
                autoSelectFirst: true,
                forceHide: true,
                onSelect: item => {
                    this.addLinkTeams(item.id, item.name);

                    $elementTeams.val('');
                },
                lookupFunction: query => {
                    if (lastAjaxPromise && lastAjaxPromise.getReadyState() < 4) {
                        lastAjaxPromise.abort();
                    }

                    lastAjaxPromise = Espo.Ajax
                        .getRequest('Team', {
                            maxSize: this.getAutocompleteMaxCount(),
                            select: 'id,name',
                            q: query,
                        });

                    return lastAjaxPromise.then(/** {list: Record[]} */response => {
                        return response.list.map(item => ({
                            id: item.id,
                            name: item.name,
                            data: item.id,
                            value: item.name,
                        }));
                    });
                },
            });

            this.once('render remove', () => autocomplete.dispose());

            const type = this.$el.find('select.search-type').val();

            if (type === 'isFromTeams') {
                this.searchData.teamIdList.forEach(id => {
                    this.addLinkTeamsHtml(id, this.searchData.teamNameHash[id]);
                });
            }
        }
    }

    deleteLinkTeams(id) {
        this.deleteLinkTeamsHtml(id);

        const index = this.searchData.teamIdList.indexOf(id);

        if (index > -1) {
            this.searchData.teamIdList.splice(index, 1);
        }

        delete this.searchData.teamNameHash[id];

        this.trigger('change');
    }

    addLinkTeams(id, name) {
        this.searchData.teamIdList = this.searchData.teamIdList || [];

        if (!~this.searchData.teamIdList.indexOf(id)) {
            this.searchData.teamIdList.push(id);
            this.searchData.teamNameHash[id] = name;
            this.addLinkTeamsHtml(id, name);

            this.trigger('change');
        }
    }

    deleteLinkTeamsHtml(id) {
        this.$el.find('.link-teams-container .link-' + id).remove();
    }

    addLinkTeamsHtml(id, name) {
        id = this.getHelper().escapeString(id);
        name = this.getHelper().escapeString(name);

        const $container = this.$el.find('.link-teams-container');

        const $el = $('<div />')
            .addClass('link-' + id)
            .addClass('list-group-item');

        $el.html(name + '&nbsp');

        $el.prepend(
            '<a role="button" class="pull-right" data-id="' + id + '" ' +
            'data-action="clearLinkTeams"><span class="fas fa-times"></a>'
        );

        $container.append($el);

        return $el;
    }

    fetchSearch() {
        const type = this.$el.find('select.search-type').val();

        if (type === 'isFromTeams') {
            return {
                type: 'isUserFromTeams',
                field: this.name,
                value: this.searchData.teamIdList,
                data: {
                    type: type,
                    teamIdList: this.searchData.teamIdList,
                    teamNameHash: this.searchData.teamNameHash,
                },
            };
        }

        return super.fetchSearch();
    }
}

export default UserFieldView;
