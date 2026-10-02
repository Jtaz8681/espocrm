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

/**
 * @internal
 */
class StickyBarHelper {


    /**
     * @param {import('views/record/detail').default} view
     * @param {string} stickButtonsFormBottomSelector
     * @param {boolean} stickButtonsContainerAllTheWay
     * @param {string} numId
     */
    constructor(view, stickButtonsFormBottomSelector, stickButtonsContainerAllTheWay, numId) {
        this.view = view;
        this.stickButtonsFormBottomSelector = stickButtonsFormBottomSelector;
        this.stickButtonsContainerAllTheWay = stickButtonsContainerAllTheWay;
        this.numId = numId;

        this.themeManager = view.getThemeManager();
        this.$el = view.$el;
    }

    init() {
        const $containers = this.$el.find('.detail-button-container');
        const $container = this.$el.find('.detail-button-container.record-buttons');

        if (!$container.length) {
            return;
        }

        const navbarHeight = this.themeManager.getParam('navbarHeight') * this.themeManager.getFontSizeFactor();
        const screenWidthXs = this.themeManager.getParam('screenWidthXs');

        const isSmallScreen = $(window.document).width() < screenWidthXs;

        const getOffsetTop = (/** JQuery */$element) => {
            let element = /** @type {HTMLElement} */$element.get(0);

            let value = 0;

            while (element) {
                value += !isNaN(element.offsetTop) ? element.offsetTop : 0;

                element = element.offsetParent;
            }

            if (isSmallScreen) {
                return value;
            }

            return value - navbarHeight;
        };

        let stickTop = getOffsetTop($container);
        const blockHeight = $container.outerHeight();

        stickTop -= 5; // padding;

        const $block = $('<div>')
            .css('height', blockHeight + 'px')
            .html('&nbsp;')
            .hide()
            .insertAfter($container);

        let $middle = this.view.getMiddleView().$el;
        const $window = $(window);
        const $navbarRight = $('#navbar .navbar-right');

        if (this.stickButtonsFormBottomSelector) {
            const $bottom = this.$el.find(this.stickButtonsFormBottomSelector);

            if ($bottom.length) {
                $middle = $bottom;
            }
        }

        $window.off('scroll.detail-' + this.numId);

        $window.on('scroll.detail-' + this.numId, () => {
            const edge = $middle.position().top + $middle.outerHeight(false) - blockHeight;
            const scrollTop = $window.scrollTop();

            if (scrollTop >= edge && !this.stickButtonsContainerAllTheWay) {
                $containers.hide();
                $navbarRight.removeClass('has-sticked-bar');
                $block.show();

                return;
            }

            if (isSmallScreen && $('#navbar .navbar-body').hasClass('in')) {
                return;
            }

            if (scrollTop > stickTop) {
                if (!$containers.hasClass('stick-sub')) {
                    $containers.addClass('stick-sub');
                    $block.show();
                }

                $navbarRight.addClass('has-sticked-bar');

                $containers.show();

                return;
            }

            if ($containers.hasClass('stick-sub')) {
                $containers.removeClass('stick-sub');
                $navbarRight.removeClass('has-sticked-bar');
                $block.hide();
            }

            $containers.show();
        });
    }
}

export default StickyBarHelper;
