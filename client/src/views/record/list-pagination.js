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

class RecordListPagination extends View {

    template = 'record/list-pagination'

    isStandaloneComponent = true

    data() {
        const total = this.collection.total;
        const offset = this.collection.offset;
        const length = this.collection.length;

        const next = this.collection.hasNextPage();
        const last = next && total >= 0;

        const from = offset + 1;
        const to = offset + length;

        const currentPageNumber = this.getCurrentPageNumber();
        const lastPageNumber = this.getLastPageNumber();

        const noTotal = !this.displayTotalCount || total < 0;

        return {
            hasGoToPage: lastPageNumber > 1 || total < 0,
            currentPageNumber: currentPageNumber,
            lastPageNumber: lastPageNumber,
            hasLastPageNumber: lastPageNumber > 1,
            total: this.getHelper().numberUtil.formatInt(total),
            from: this.getHelper().numberUtil.formatInt(from),
            to: this.getHelper().numberUtil.formatInt(to),
            previous: this.collection.hasPreviousPage(),
            next: next,
            last: last,
            noTotal: noTotal,
            noData: to === 0,
        };
    }

    setup() {
        this.recordView = /** @type {import('views/record/list').default} */this.options.recordView;

        this.listenTo(this.collection, 'update', () => {
            if (!this.element) {
                // A hack. Prevents warnings in console.
                return;
            }

            this.reRender();
        });


        this.addHandler('change', 'input.page-input', (e, /** HTMLInputElement */input) => {
            if (input.value === '') {
                input.value = this.getCurrentPageNumber();

                return;
            }

            const result = this.goToNumber(parseInt(input.value));

            if (!result) {
                input.value = this.getCurrentPageNumber();
            }
        });

        this.addHandler('focus', 'input.page-input', (e, /** HTMLInputElement */input) => {
            input.select();
        });

        this.addHandler('input', 'input.page-input', (e, /** HTMLInputElement */input) => {
            input.value = input.value.replace(/[^0-9.]/g, '');
        });

        this.addHandler('click', '.page-input-group > .input-group-addon', e => {
            e.preventDefault();
            e.stopImmediatePropagation();
        });

        this.displayTotalCount = this.options.displayTotalCount;
    }

    /**
     * @return {number|null}
     */
    getCurrentPageNumber() {
        return Math.floor(this.collection.offset / this.collection.maxSize) + 1;
    }

    /**
     * @return {number|null}
     */
    getLastPageNumber() {
        return this.collection.total >= 0 ?
            Math.floor(this.collection.total / this.collection.maxSize) + 1 :
            null;
    }

    /**
     * @param {Number} number
     * @return {Promise|null}
     */
    goToNumber(number) {
        const offset = (number - 1) * this.collection.maxSize;

        if (this.collection.total >= 0 && offset > this.collection.total) {
            Espo.Ui.warning(this.translate('pageNumberIsOutOfBound', 'messages'));

            return null;
        }

        Espo.Ui.notifyWait();

       return this.collection.setOffset(offset)
            .then(() => {
                Espo.Ui.notify(false);

                this.recordView.trigger('after:paginate');
            });
    }
}

export default RecordListPagination;
