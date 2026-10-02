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

import $ from 'jquery';
import {Events} from 'bullbone';

/**
 * @internal
 */
class StickyBarHelper {

    /** @private */
    $bar
    /** @private */
    $scrollable
    /** @private */
    $window
    /** @private */
    $navbarRight
    /** @private */
    $middle
    /** @private */
    _isReady = false

    /**
     * @param {import('views/record/list-base').default} view
     * @param {{force?: boolean}} options
     */
    constructor(view, options = {}) {
        this.view = view;

        /**
         * @private
         * @type {import('theme-manager').default}
         */

        this.themeManager = this.view.getThemeManager();

        this.$el = view.$el;

        /** @private */
        this.force = options.force || false;

        this.init();
    }

    init() {
        this.$bar = this.$el.find('.sticked-bar');
        this.$middle = this.$el.find('> .list');

        if (!this.$middle.get(0)) {
            return;
        }

        this.$window = $(window);
        this.$scrollable = this.$window;
        this.$navbarRight = $('#navbar .navbar-right');

        this.isModal = !!this.$el.closest('.modal-body').length;

        this.isSmallWindow = $(window.document).width() < this.themeManager.getParam('screenWidthXs');

        if (this.isModal) {
            this.$scrollable = this.$el.closest('.modal-body');
            this.$navbarRight = this.$scrollable.parent().find('.modal-footer');
        }

        if (!this.force) {
            this.$scrollable.off(`scroll.list-${this.view.cid}`);
            this.$scrollable.on(`scroll.list-${this.view.cid}`, () => this._controlSticking());

            this.$window.off(`resize.list-${this.view.cid}`);
            this.$window.on(`resize.list-${this.view.cid}`, () => this._controlSticking());
        }

        this.listenTo(this.view, 'check', () => {
            if (this.view.getCheckedIds().length === 0 && !this.view.isAllResultChecked()) {
                return;
            }

            this._controlSticking();
        });

        this._isReady = true;
    }

    _getMiddleTop() {
        if (this._middleTop !== undefined && this._middleTop >= 0) {
            return this._middleTop;
        }

        this._middleTop = this._getOffsetTop(this.$middle.get(0));

        return this._middleTop;
    }

    _getButtonsTop() {
        if (this._buttonsTop !== undefined && this._buttonsTop >= 0) {
            return this._buttonsTop;
        }

        this._buttonsTop = this._getOffsetTop(this.$el.find('.list-buttons-container').get(0));

        return this._buttonsTop;
    }

    /**
     * @private
     */
    _controlSticking() {
        if (!this.view.toShowStickyBar()) {
            return;
        }

        if (this.isSmallWindow && $('#navbar .navbar-body').hasClass('in')) {
            return;
        }

        const scrollTop = this.$scrollable.scrollTop();
        const stickTop = !this.force ? this._getButtonsTop() : 0;
        const edge = this._getMiddleTop() + this.$middle.outerHeight(true);

        const hide = () => {
            this.$bar.addClass('hidden');
            this.$navbarRight.removeClass('has-sticked-bar');
        };

        const show = () => {
            this.$bar.removeClass('hidden');
            this.$navbarRight.addClass('has-sticked-bar');
        };

        if (scrollTop >= edge) {
            hide();

            return;
        }

        if (scrollTop > stickTop || this.force) {
            show();

            return;
        }

        hide();
    }

    /**
     * @private
     * @param {HTMLElement} element
     */
    _getOffsetTop(element) {
        if (!element) {
            return 0;
        }

        const navbarHeight = this.themeManager.getParam('navbarHeight') * this.themeManager.getFontSizeFactor();
        const withHeader = !this.isSmallWindow && !this.isModal;

        let offsetTop = 0;

        do {
            if (element.classList.contains('modal-body')) {
                break;
            }

            if (!isNaN(element.offsetTop)) {
                offsetTop += element.offsetTop;
            }

            element = element.offsetParent;
        } while (element);

        if (withHeader) {
            offsetTop -= navbarHeight;
        }

        if (!this.isModal) {
            // padding
            offsetTop -= 5;
        }

        return offsetTop;
    }

    hide() {
        this.$bar.addClass('hidden');
    }

    destroy() {
        this.stopListening(this.view, 'check');

        if (!this._isReady) {
            return;
        }

        this.$window.off(`resize.list-${this.view.cid}`);
        this.$scrollable.off(`scroll.list-${this.view.cid}`);
    }
}

Object.assign(StickyBarHelper.prototype, Events);

export default StickyBarHelper;
