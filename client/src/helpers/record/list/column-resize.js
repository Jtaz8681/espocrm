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

/**
 * @internal
 */
export default class ListColumnResizeHelper {

    /**
     * @type {{
     *     startX: number,
     *     startWidth: number,
     *     thElement: HTMLTableCellElement,
     *     name: string,
     *     inPx: boolean,
     *     onRight: boolean,
     *     newWidth: number|null,
     *     thElements: HTMLTableCellElement[],
     * }}
     * @private
     */
    item

    /**
     * A min width in pixels.
     *
     * @private
     * @type {number}
     */
    minWidth = 30

    static selector = 'table > thead > tr > th > .column-resizer';

    /**
     * @param {import('views/record/list-base').default} view
     * @param {import('helpers/list/settings').default} helper
     */
    constructor(view, helper) {
        /** @private */
        this.view = view;
        /** @private */
        this.helper = helper;

        /**
         * @private
         * @type {number}
         */
        this.fontSizeFactor = view.getThemeManager().getFontSizeFactor();

        this.onPointerUpBind = this.onPointerUp.bind(this);
        this.onPointerMoveBind = this.onPointerMove.bind(this);

        view.addHandler('pointerdown', ListColumnResizeHelper.selector, (/** PointerEvent */e, target) => {
            this.onPointerDown(e, target);
        });
    }

    /**
     * @private
     * @param {PointerEvent} event
     * @param {HTMLElement} target
     */
    onPointerDown(event, target) {
        if (!event.isPrimary) {
            return;
        }

        this.startResizeInit(event, target)

        window.addEventListener('pointerup', this.onPointerUpBind);
        window.addEventListener('pointermove', this.onPointerMoveBind);
    }

    /**
     * @private
     * @param {PointerEvent} event
     * @param {HTMLElement} target
     */
    startResizeInit(event, target) {
        const th = /** @type {HTMLTableCellElement} */target.parentNode;

        const thElements = [...th.parentNode.querySelectorAll(':scope > th.field-header-cell')]
            .filter(it => !it.style.width);

        this.item = {
            startX: event.clientX,
            startWidth: th.clientWidth,
            thElement: th,
            name: th.dataset.name,
            inPx: th.style.width && th.style.width.endsWith('px'),
            onRight: target.classList.contains('column-resizer-right'),
            newWidth: null,
            thElements: thElements,
        };

        document.body.style.cursor = 'col-resize';

        const trElement = this.item.thElement.closest('tr');

        trElement.classList.add('being-column-resized');
        this.item.thElement.classList.add('being-resized');
    }

    /**
     * @private
     * @param {number} width
     */
    isWidthOk(width) {
        if (width < this.minWidth * this.fontSizeFactor) {
            return false;
        }

        for (const th of this.item.thElements) {
            if (th.style.width) {
                continue;
            }

            if (th.clientWidth < this.minWidth * this.fontSizeFactor) {
                return false;
            }
        }

        return true;
    }

    /**
     * @private
     * @param {PointerEvent} event
     */
    onPointerMove(event) {
        let diff = event.clientX - this.item.startX;

        if (!this.item.onRight) {
            diff *= -1;
        }

        const width = this.item.startWidth + diff;

        if (!this.isWidthOk(width)) {
            return;
        }

        const previousWidth = this.item.newWidth;
        const previousStyleWidth = this.item.thElement.style.width;

        this.item.newWidth = width;
        this.item.thElement.style.width = width.toString() + 'px';

        if (!this.isWidthOk(width)) {
            if (previousWidth) {
                this.item.newWidth = previousWidth;
            }

            this.item.thElement.style.width = previousStyleWidth;
        }
    }

    /**
     * @private
     */
    onPointerUp() {
        window.removeEventListener('pointermove', this.onPointerMoveBind);
        window.removeEventListener('pointerup', this.onPointerUpBind);
        document.body.style.cursor = '';

        const width = this.item.newWidth;

        if (width === null) {
            this.disableResizingState();

            return;
        }

        let unit = 'px';
        let value = width;

        if (!this.item.inPx) {
            const tableElement = this.item.thElement.closest('table');

            const tableWidth = tableElement.clientWidth;

            const factor = Math.pow(10, 4);
            const widthPercents = width / tableWidth;
            const widthPercentsRounded = Math.floor(factor * widthPercents * 100) / factor;

            this.item.thElement.style.width = widthPercentsRounded.toString() + '%';

            unit = '%';
            value = widthPercentsRounded;
        }

        if (this.item.inPx) {
            value = value / this.fontSizeFactor;
        }

        this.helper.storeColumnWidth(this.item.name, {value: value, unit: unit});

        this.disableResizingState();
    }

    /**
     * @private
     */
    disableResizingState() {
        const trElement = this.item.thElement.closest('tr')

        trElement.classList.remove('being-column-resized');
        this.item.thElement.classList.remove('being-resized');

        this.item = undefined;
    }
}
