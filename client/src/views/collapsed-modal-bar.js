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

import View from 'view';
import CollapsedModalView from 'views/collapsed-modal';
import PopupNotificationView from 'views/popup-notification';

class CollapsedModalBarView extends View {

    // language=Handlebars
    templateContent = `
        {{#each dataList}}
            <div class="collapsed-modal" data-number="{{number}}">{{var key ../this}}</div>
        {{/each}}
    `

    /**
     * @private
     * @type {number}
     */
    maxNumberToDisplay = 3

    /**
     * @private
     * @type {number[]}
     */
    numberList

    /**
     * @private
     * @type {number}
     */
    lastNumber

    /**
     * @private
     * @type {WeakMap<import('view').default, number>}
     */
    map

    data() {
        return {
            dataList: this.getDataList(),
        };
    }

    init() {
        this.on('render', () => {
            if (document.querySelector('.collapsed-modal-bar')) {
                return;
            }

            const div = document.createElement('div');
            div.classList.add('collapsed-modal-bar');

            document.body.append(div);
        });
    }

    setup() {
        this.lastNumber = 0;
        this.numberList = [];

        this.map = new WeakMap();
    }

    /**
     * @private
     * @return {Record[]}
     */
    getDataList() {
        const list = [];

        let numberList = [...this.numberList];

        if (this.numberList.length > this.maxNumberToDisplay) {
            numberList = numberList.slice(this.numberList.length - this.maxNumberToDisplay);
        }

        numberList
            .reverse()
            .forEach((number, i) => {
                list.push({
                    number: number.toString(),
                    key: this.composeKey(number),
                    index: i,
                });
            });

        return list;
    }

    /**
     * @private
     * @param {string} title
     * @return {number|null}
     */
    calculateDuplicateNumber(title) {
        let duplicateNumber = 0;

        for (const number of this.numberList) {
            const view = this.getCollapsedModalViewByNumber(number);

            if (!view) {
                continue;
            }

            if (view.title === title) {
                duplicateNumber++;
            }
        }

        if (duplicateNumber === 0) {
            return null;
        }

        return duplicateNumber;
    }

    /**
     * @param {number} number
     * @return {import('views/collapsed-modal').default|null}
     */
    getCollapsedModalViewByNumber(number) {
        const key = this.composeKey(number);

        return this.getView(key);
    }

    /**
     * @type {import('views/modal').default[]}
     */
    getModalViewList() {
        return this.numberList
            .map(number => this.getCollapsedModalViewByNumber(number))
            .filter(it => it)
            .map(it => it.modalView);
    }

    /**
     * @param {import('views/modal').default|import('views/popup-notification').default} modalView
     * @param {{title: string | null}} options
     */
    async addModalView(modalView, options) {
        const number = this.lastNumber;

        this.numberList.push(this.lastNumber);

        const key = this.composeKey(number);

        this.lastNumber++;

        this.map.set(modalView, number);

        const view = new CollapsedModalView({
            modalView: modalView,
            title: options.title,
            duplicateNumber: this.calculateDuplicateNumber(options.title),
            onClose: () => {
                this.removeModalViewByNumber(number);

                if (modalView instanceof PopupNotificationView) {
                    modalView.resolveCancel();
                }
            },
            onExpand: () => {
                this.removeModalViewByNumber(number, true);

                // Use timeout to prevent DOM being updated after modal is re-rendered.
                setTimeout(async () => {
                    if (modalView instanceof PopupNotificationView) {
                        this.expandPopupNotification(modalView);

                        return;
                    }

                    const key = `dialog-${number}`;

                    this.setView(key, modalView);
                    modalView.setSelector(modalView.containerSelector);

                    await this.getView(key).render();

                    modalView.trigger('after:expand');
                }, 5);
            },
        });

        await this.assignView(key, view, `[data-number="${number}"]`);

        await this.reRender(true);
    }

    /**
     * @param {import('views/modal').default|import('views/popup-notification').default} modalView
     * @since 10.0
     */
    removeModalView(modalView) {
        const number = this.map.get(modalView);

        if (number === undefined) {
            return;
        }

        this.removeModalViewByNumber(number);
    }

    /**
     * @param {number} number
     * @param {boolean} [noReRender]
     */
    removeModalViewByNumber(number, noReRender = false) {
        const key = this.composeKey(number);

        const index = this.numberList.indexOf(number);

        if (~index) {
            this.numberList.splice(index, 1);
        }

        if (this.isRendered()) {
            const element = this.element.querySelector(`.collapsed-modal[data-number="${number}"]`);

            if (element) {
                element.remove();
            }
        }

        if (!noReRender) {
            this.reRender();
        }

        const view = this.getCollapsedModalViewByNumber(number);

        if (view?.modalView) {
            this.getRouter().removeWindowLeaveOutObject(view.modalView);
        }

        this.clearView(key);
    }

    /**
     * @private
     * @param {number} number
     * @return {string}
     */
    composeKey(number) {
        return `key-${number}`;
    }

    /**
     * @private
     * @param {PopupNotificationView} view
     */
    expandPopupNotification(view) {
        view.expand();
    }
}

export default CollapsedModalBarView;
