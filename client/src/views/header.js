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

/** @module views/header */

import View from 'view';
import HeaderButtonsView from 'views/main/header-buttons';

class HeaderView extends View {

    template = 'header'

    /**
     * @type {string}
     * @private
     */
    scope

    data() {
        const data = {};

        if ('getHeader' in this.getParentMainView()) {
            data.header = this.getParentMainView().getHeader();
        }

        data.scope = this.scope || this.getParentMainView().scope;
        data.items = this.getItems();

        const dropdown = (data.items || {}).dropdown || [];

        data.hasVisibleDropdownItems = false;

        dropdown.forEach(item => {
            if (!item.hidden) {
                data.hasVisibleDropdownItems = true;
            }
        });

        data.noBreakWords = this.options.fontSizeFlexible;
        data.isXsSingleRow = this.options.isXsSingleRow;
        data.menuItemsHidden = this.menuItemsHidden;

        if ((data.items.buttons || []).length < 2) {
            data.isHeaderAdditionalSpace = true;
        }

        return data;
    }

    setup() {
        this.scope = this.options.scope;

        this.setupButtons();

        if (this.model) {
            this.listenTo(this.model, 'after:save', () => {
                if (this.isRendered()) {
                    this.reRender({buffer: true});
                }
            });
        }

        this.wasRendered = false;

        if (this.options.fontSizeFlexible) {
            this.on('action-item-update', () => {
                if (this.isRendered()) {
                    this.adjustFontSize();
                }
            });
        }
    }

    /**
     * Hide all menu items.
     *
     * @return {Promise}
     */
    async hideAllMenuItems() {
        this.menuItemsHidden = true;

        await this.reRenderButtons();

        this.adjustFontSize();
    }

    /**
     * Show all menu items.
     *
     * @return {Promise}
     */
    async showAllActionItems() {
        this.menuItemsHidden = false;

        await this.reRenderButtons();

        this.adjustFontSize();
    }

    afterRender() {
        if (this.options.fontSizeFlexible) {
            /** @private */
            this.$headerBreadcrumps = this.$el.find('.header-breadcrumbs');
            this._titleIsInitiated = false;

            this.adjustFontSize();
        }

        if (this.wasRendered) {
            this.getParentMainView().trigger('header-rendered');
        }

        this.wasRendered = true;
    }

    /**
     * @private
     * @param {number} step
     */
    adjustFontSize(step = 0) {
        if (!step) {
            this.fontSizePercentage = 100;
        }

        const $container = this.$headerBreadcrumps;

        const containerWidth = $container.width();
        let childrenWidth = 0;

        $container.children().each((i, el) => {
            childrenWidth += $(el).outerWidth(true);
        });

        if (containerWidth >= childrenWidth) {
            return;
        }

        if (step > 7) {
            if (this._titleIsInitiated) {
                return;
            }

            this._titleIsInitiated = true;

            $container.addClass('overlapped');

            this.$el.find('.title').each((i, el) => {
                const $el = $(el);
                const text = $(el).text();

                $el.attr('title', text);

                let isInitialized = false;

                $el.on('touchstart', () => {
                    if (!isInitialized) {
                        $el.attr('title', '');
                        isInitialized = true;

                        Espo.Ui.popover($el, {
                            content: text,
                            noToggleInit: true,
                        }, this);
                    }

                    $el.popover('toggle');
                });
            });

            return;
        }

        this.fontSizePercentage -= 4;
        const $flexible = this.$el.find('.font-size-flexible');

        $flexible.css('font-size', this.fontSizePercentage + '%');
        $flexible.css('position', 'relative');

        if (step > 6) {
            $flexible.css('top', '-1px');
        } else if (step > 4) {
            $flexible.css('top', '-1px');
        }

        this.adjustFontSize(step + 1);
    }

    /**
     * @private
     * @returns {{
     *     buttons?: import('views/main').MenuItem[],
     *     dropdown?: import('views/main').MenuItem[],
     *     actions?: import('views/main').MenuItem[],
     * }}
     */
    getItems() {
        return this.getParentMainView().getMenu() || {};
    }

    /**
     * @return {import('views/main').default}
     */
    getParentMainView() {
        return /** @type {import('views/main').default} */this.getParentView();
    }

    /**
     * @private
     */
    setupButtons() {
        const view = new HeaderButtonsView({
            scope: this.scope ?? this.model?.entityType ?? null,
            dataProvider: () => {
                const items = this.getItems();

                return {
                    buttons: items.buttons ?? [],
                    actions: items.actions ?? [],
                    dropdown: items.dropdown ?? [],
                    hidden: this.menuItemsHidden,
                };
            },
        });

        this.assignView('buttons', view);
    }

    /**
     * @private
     * @return {HeaderButtonsView}
     */
    getButtonsView() {
        return this.getView('buttons');
    }

    /**
     * Re-render buttons.
     *
     * @since 10.0.0
     */
    async reRenderButtons() {
        if (!this.isReady) {
            return;
        }

        await this.getButtonsView()?.reRender({buffer: true});
    }
}

export default HeaderView;
