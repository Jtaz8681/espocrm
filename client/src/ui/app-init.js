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

function uiAppInit() {
    const $document = $(document);

    const topSpaceHeight = 100;

    $document.on('keydown.espo.button', e => {
        if (
            e.code !== 'Enter' ||
            e.target.tagName !== 'A' ||
            e.target.getAttribute('role') !== 'button' ||
            e.target.getAttribute('href') ||
            e.ctrlKey ||
            e.altKey ||
            e.metaKey
        ) {
            return;
        }

        $(e.target).click();

        e.preventDefault();
    });

    $document.on('hidden.bs.dropdown', e => {
        $(e.target).removeClass('dropup');
    });

    $document.on('show.bs.dropdown', e => {
        let isUp;

        /** @type {HTMLElement} */
        const target = e.target;
        const $dropdown = $(e.target).find('.dropdown-menu');

        /** @type {HTMLElement} */
        const dropdownElement = $dropdown.get(0);

        if (!dropdownElement) {
            return;
        }

        const height = $dropdown.outerHeight();
        const width = $dropdown.outerWidth();

        {
            const $target = $(target);

            const windowHeight = $(window).height();
            const top = e.target.getBoundingClientRect().bottom;

            const spaceBelow = windowHeight - (top + height);

            isUp = spaceBelow < 0 && top - topSpaceHeight > height;

            if ($target.hasClass('more') || $target.hasClass('tab')) {
                return;
            }

            if (isUp) {
                $target.addClass('dropup');
            } else {
                $target.removeClass('dropup');
            }
        }

        if (
            dropdownElement.classList.contains('pull-right') &&
            target.getBoundingClientRect().left - width < 0
        ) {
            const maxWidth = target.getBoundingClientRect().right - target.getBoundingClientRect().width / 2;

            dropdownElement.style.maxWidth = maxWidth + 'px';

            const $group = $(target);

            $group.one('hidden.bs.dropdown', () => {
                dropdownElement.style.maxWidth = '';
            });

            return;
        }

        const $dashletBody = $(target).closest('.dashlet-body');

        const fixPosition = e.target.parentElement.classList.contains('fix-position');

        if ($dashletBody.length || fixPosition) {
            let $body = $dashletBody;

            if (fixPosition) {
                $body = $(window);
            }

            $(target).removeClass('dropup');

            const $group = $(target);

            const rect = target.getBoundingClientRect();
            const $ul = $group.find('.dropdown-menu');
            const isRight = target.classList.contains('pull-right');

            const $toggle = $group.find('.dropdown-toggle');

            $body.on('scroll.dd', () => {
                if ($group.hasClass('open')) {
                    // noinspection JSUnresolvedReference
                    $toggle.dropdown('toggle');
                    $body.off('scroll.dd');
                }
            })

            $group.one('hidden.bs.dropdown', () => {
                $body.off('scroll.dd');
            });

            const left = isRight ?
                rect.left - $ul.outerWidth() + rect.width:
                rect.left

            const top = isUp ?
                rect.top - height :
                rect.top + target.getBoundingClientRect().height;

            $ul.css({
                position: 'fixed',
                top: top,
                left: left,
                right: 'auto',
            });

            //return;
        }

        /*if (e.target.parentElement.classList.contains('fix-overflow')) {
            $(target).removeClass('dropup');

            const isRight = e.target.classList.contains('pull-right');

            const $ul = $(e.target.parentElement).find('.dropdown-menu');

            const rect = e.target.getBoundingClientRect();

            const parent = $ul.offsetParent().get(0);

            if (!parent) {
                return;
            }

            const scrollTop = parent === window.document.documentElement ?
                (document.documentElement.scrollTop || document.body.scrollTop) :
                parent.scrollTop;

            const top = isUp ?
                rect.top + scrollTop - height :
                rect.top + scrollTop + e.target.getBoundingClientRect().height;

            const left = isRight ?
                rect.left - $ul.outerWidth() + rect.width:
                rect.left

            $ul.css({
                top: top,
                left: left,
                right: 'auto',
            });
        }*/
    });
}

export default uiAppInit;
