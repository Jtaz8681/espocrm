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

import LinkMultipleFieldView from 'views/fields/link-multiple';

/**
 * A link-multiple field with a primary.
 */
class LinkMultipleWithPrimaryFieldView extends LinkMultipleFieldView {

    /**
     * @protected
     * @type {string}
     */
    primaryLink

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

    /**
     * @inheritDoc
     */
    getAttributeList() {
        const list = super.getAttributeList();

        list.push(this.primaryIdAttribute);
        list.push(this.primaryNameAttribute);

        return list;
    }

    setup() {
        this.primaryLink = this.options.primaryLink || this.primaryLink ||
            this.model.getFieldParam(this.name, 'primaryLink');

        this.primaryIdAttribute = this.primaryLink + 'Id';
        this.primaryNameAttribute = this.primaryLink + 'Name';

        super.setup();

        this.primaryId = this.model.get(this.primaryIdAttribute);
        this.primaryName = this.model.get(this.primaryNameAttribute);

        this.listenTo(this.model, 'change:' + this.primaryIdAttribute, () => {
            this.primaryId = this.model.get(this.primaryIdAttribute);
            this.primaryName = this.model.get(this.primaryNameAttribute);
        });

        this.events['click [data-action="switchPrimary"]'] = e => {
            const $target = $(e.currentTarget);
            const id = $target.data('id');

            this.switchPrimary(id);
        };
    }

    /**
     * @protected
     * @param {string|null} id An ID.
     */
    setPrimaryId(id) {
        this.primaryId = id;

        this.primaryName = id ?
            this.nameHash[id] : null;
    }

    /**
     * @protected
     */
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

    /**
     * @inheritDoc
     */
    getValueForDisplay() {
        if (this.isDetailMode() || this.isListMode()) {
            const itemList = [];

            if (this.primaryId) {
                itemList.push(this.getDetailLinkHtml(this.primaryId, this.primaryName));
            }

            if (!this.ids.length) {
                return;
            }

            this.ids.forEach(id => {
                if (id !== this.primaryId) {
                    itemList.push(this.getDetailLinkHtml(id));
                }
            });

            return itemList
                .map(item => $('<div>')
                    .addClass('link-multiple-item')
                    .append(item).get(0).outerHTML)
                .join('');
        }
    }

    /**
     * @inheritDoc
     */
    deleteLink(id) {
        if (id === this.primaryId) {
            this.setPrimaryId(null);
        }

        super.deleteLink(id);
    }

    /**
     * @inheritDoc
     */
    deleteLinkHtml(id) {
        super.deleteLinkHtml(id);

        this.managePrimaryButton();
    }

    /**
     * @inheritDoc
     */
    addLinkHtml(id, name) {
        // Do not use the `html` method to avoid XSS.

        name = name || id;

        if (this.isSearchMode()) {
            return super.addLinkHtml(id, name);
        }

        const $container = this.$el.find('.link-container');

        const $el = $('<div>')
            .addClass('form-inline clearfix ')
            .addClass('list-group-item link-with-role link-group-item-with-primary')
            .addClass('link-' + id)
            .attr('data-id', id);

        const $name = $('<div>').text(name).append('&nbsp;');

        const $remove = $('<a>')
            .attr('role', 'button')
            .attr('tabindex', '0')
            .attr('data-id', id)
            .attr('data-action', 'clearLink')
            .addClass('pull-right')
            .append(
                $('<span>').addClass('fas fa-times')
            );

        const $left = $('<div>');
        const $right = $('<div>');

        $left.append($name);
        $right.append($remove);

        $el.append($left);
        $el.append($right);

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

        $button.insertBefore($el.children().first().children().first());

        $container.append($el);

        this.managePrimaryButton();

        return $el;
    }

    /**
     * @protected
     */
    managePrimaryButton() {
        const $primary = this.$el.find('button[data-action="switchPrimary"]');

        if ($primary.length > 1) {
            $primary.removeClass('hidden');
        }
        else {
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

        // noinspection JSValidateTypes
        return data;
    }
}

export default LinkMultipleWithPrimaryFieldView;
