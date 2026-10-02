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
import LinkMultipleWithPrimaryFieldView from 'views/fields/link-multiple-with-primary';

/**
 * A link-multiple field with columns and a primary.
 */
class LinkMultipleWithColumnsWithPrimaryFieldView extends LinkMultipleWithColumnsFieldView {

    /**
     * @protected
     * @type {string}
     */
    primaryLink

    getAttributeList() {
        const list = super.getAttributeList();

        list.push(this.primaryIdAttribute);
        list.push(this.primaryNameAttribute);

        return list;
    }

    setup() {
        this.primaryLink = this.primaryLink || this.model.getFieldParam(this.name, 'primaryLink');

        this.primaryIdAttribute = this.primaryLink + 'Id';
        this.primaryNameAttribute = this.primaryLink + 'Name';

        super.setup();

        this.events['click [data-action="switchPrimary"]'] = e => {
            const $target = $(e.currentTarget);
            const id = $target.data('id');

            this.switchPrimary(id);
        };

        this.primaryId = this.model.get(this.primaryIdAttribute);
        this.primaryName = this.model.get(this.primaryNameAttribute);

        this.listenTo(this.model, 'change:' + this.primaryIdAttribute, () => {
            this.primaryId = this.model.get(this.primaryIdAttribute);
            this.primaryName = this.model.get(this.primaryNameAttribute);
        });
    }

    setPrimaryId(id) {
        this.primaryId = id;

        this.primaryName = id ?
            this.nameHash[id] : null;
    }

    switchPrimary(id) {
        const $switch = this.$el.find(`[data-id="${id}"][data-action="switchPrimary"]`);

        if (!$switch.hasClass('active')) {
            this.$el.find('button[data-action="switchPrimary"]')
                .removeClass('active')
                .children()
                .addClass('text-muted');

            $switch.addClass('active').children().removeClass('text-muted');

            this.setPrimaryId(id);

            this.trigger('change');
        }
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
                itemList.push(
                    this.getDetailLinkHtml(this.primaryId, this.primaryName)
                );
            }

            if (!this.ids.length) {
                return;
            }

            this.ids.forEach(id =>{
                if (id !== this.primaryId) {
                    itemList.push(
                        this.getDetailLinkHtml(id)
                    );
                }
            });

            return itemList
                .map(item => $('<div>').append(item).addClass('link-multiple-item').get(0).outerHTML)
                .join('');
        }
    }

    deleteLink(id) {
        if (id === this.primaryId) {
            this.setPrimaryId(null);
        }

        super.deleteLink(id);
    }

    deleteLinkHtml(id) {
        super.deleteLinkHtml(id);

        this.managePrimaryButton();
    }

    addLinkHtml(id, name) {
        name = name || id;

        if (this.isSearchMode()) {
            return super.addLinkHtml(id, name);
        }

        if (this.skipRoles) {
            return LinkMultipleWithPrimaryFieldView.prototype.addLinkHtml.call(this, id, name);
        }

        const $el = super.addLinkHtml(id, name);

        const isPrimary = (id === this.primaryId);

        const $star = $('<span>')
            .addClass('fas fa-star fa-sm')
            .addClass(!isPrimary ? 'text-muted' : '');

        const $button = $('<button>')
            .attr('type', 'button')
            .addClass('btn btn-link btn-sm pull-right hidden')
            .attr('title', this.translate('Primary'))
            .attr('data-action', 'switchPrimary')
            .attr('data-id', id)
            .append($star);

        $button.insertAfter($el.children().first().children().first());

        this.managePrimaryButton();

        return $el;
    }

    managePrimaryButton() {
        const $primary = this.$el.find('button[data-action="switchPrimary"]');

        if ($primary.length > 1) {
            $primary.removeClass('hidden');
        } else {
            $primary.addClass('hidden');
        }

        if ($primary.filter('.active').length === 0) {
            const $first = $primary.first();

            if ($first.length) {
                $first.addClass('active').children().removeClass('text-muted');

                const id = $first.data('id');

                this.setPrimaryId(id);

                if (id !== this.primaryId) {
                    this.trigger('change');
                }
            }
        }
    }

    fetch() {
        const data = super.fetch();

        data[this.primaryIdAttribute] = this.primaryId;
        data[this.primaryNameAttribute] = this.primaryName;

        return data;
    }
}

// noinspection JSUnusedGlobalSymbols
export default LinkMultipleWithColumnsWithPrimaryFieldView;
