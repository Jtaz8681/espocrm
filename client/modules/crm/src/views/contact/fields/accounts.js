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

import LinkMultipleWithColumnsFieldView from 'views/fields/link-multiple-with-columns';

class AccountsFieldView extends LinkMultipleWithColumnsFieldView {

    getAttributeList() {
        const list = super.getAttributeList();

        list.push('accountId');
        list.push('accountName');
        list.push('title');

        return list;
    }

    setup() {
        super.setup();

        this.events['click [data-action="switchPrimary"]'] = e => {
            const $target = $(e.currentTarget);
            const id = $target.data('id');

            if (!$target.hasClass('active')) {
                this.$el.find('button[data-action="switchPrimary"]')
                    .removeClass('active')
                    .children()
                    .addClass('text-muted');

                $target.addClass('active')
                    .children()
                    .removeClass('text-muted');

                this.setPrimaryId(id);
            }
        };

        this.primaryIdFieldName = 'accountId';
        this.primaryNameFieldName = 'accountName';
        this.primaryRoleFieldName = 'title';

        this.primaryId = this.model.get(this.primaryIdFieldName);
        this.primaryName = this.model.get(this.primaryNameFieldName);

        this.listenTo(this.model, 'change:' + this.primaryIdFieldName, () => {
            this.primaryId = this.model.get(this.primaryIdFieldName);
            this.primaryName = this.model.get(this.primaryNameFieldName);
        });

        if (this.isEditMode() || this.isDetailMode()) {
            this.events['click a[data-action="setPrimary"]'] = (e) => {
                const id = $(e.currentTarget).data('id');

                this.setPrimaryId(id);
                this.reRender();
            }
        }
    }

    setPrimaryId(id) {
        this.primaryId = id;

        if (id) {
            this.primaryName = this.nameHash[id];
        } else {
            this.primaryName = null;
        }

        this.trigger('change');
    }

    renderLinks() {
        if (this.primaryId) {
            this.addLinkHtml(this.primaryId, this.primaryName);
        }

        this.ids.forEach(id => {
            if (id !== this.primaryId) {
                this.addLinkHtml(id, this.nameHash[id]);
            }
        });
    }

    getValueForDisplay() {
        if (this.isDetailMode() || this.isListMode()) {
            const itemList = [];

            if (this.primaryId) {
                itemList.push(this.getDetailLinkHtml(this.primaryId, this.primaryName));
            }

            this.ids.forEach(id => {
                if (id !== this.primaryId) {
                    itemList.push(this.getDetailLinkHtml(id));
                }
            });

            return itemList
                .map(item => {
                    return $('<div>')
                        .addClass('link-multiple-item')
                        .html(item)
                        .get(0).outerHTML;
                })
                .join('');
        }
    }

    getDetailLinkHtml(id, name) {
        const html = super.getDetailLinkHtml(id, name);

        if (this.getColumnValue(id, 'isInactive')) {
            const $el = $('<div>').html(html);

            $el.find('a').css('text-decoration', 'line-through');

            return $el.get(0).innerHTML;
        }

        return html;
    }

    afterAddLink(id) {
        super.afterAddLink(id);

        if (this.ids.length === 1) {
            this.primaryId = id;
            this.primaryName = this.nameHash[id];
        }

        this.controlPrimaryAppearance();
    }

    afterDeleteLink(id) {
        super.afterDeleteLink(id);

        if (this.ids.length === 0) {
            this.primaryId = null;
            this.primaryName = null;

            return;
        }

        if (id === this.primaryId) {
            this.primaryId = this.ids[0];
            this.primaryName = this.nameHash[this.primaryId];
        }

        this.controlPrimaryAppearance();
    }

    controlPrimaryAppearance() {
        this.$el.find('li.set-primary-list-item').removeClass('hidden');

        if (this.primaryId) {
            this.$el.find('li.set-primary-list-item[data-id="'+this.primaryId+'"]').addClass('hidden');
        }
    }

    addLinkHtml(id, name) {
        name = name || id;

        if (this.isSearchMode()) {
            return super.addLinkHtml(id, name);
        }

        const $el = super.addLinkHtml(id, name);

        const isPrimary = id === this.primaryId;

        const $a = $('<a>')
            .attr('role', 'button')
            .attr('tabindex', '0')
            .attr('data-action', 'setPrimary')
            .attr('data-id', id)
            .text(this.translate('Set Primary', 'labels', 'Account'));

        const $li = $('<li>')
            .addClass('set-primary-list-item')
            .attr('data-id', id)
            .append($a);

        if (isPrimary || this.ids.length === 1) {
            $li.addClass('hidden');
        }

        $el.find('ul.dropdown-menu').append($li);

        if (this.getColumnValue(id, 'isInactive')) {
            $el.find('div.link-item-name').css('text-decoration', 'line-through');
        }
    }

    fetch() {
        const data = super.fetch();

        data[this.primaryIdFieldName] = this.primaryId;
        data[this.primaryNameFieldName] = this.primaryName;
        data[this.primaryRoleFieldName] = (this.columns[this.primaryId] || {}).role || null;

        // noinspection JSUnresolvedReference
        data.accountIsInactive = (this.columns[this.primaryId] || {}).isInactive || false;

        if (!this.primaryId) {
            data[this.primaryRoleFieldName] = null;
            data.accountIsInactive = null;
        }

        return data;
    }
}

export default AccountsFieldView;
