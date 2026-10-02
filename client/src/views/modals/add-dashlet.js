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

import ModalView from 'views/modal';

class AddDashletModalView extends ModalView {

    template = 'modals/add-dashlet'

    cssName = 'add-dashlet'
    backdrop = true

    events = {
        /** @this AddDashletModalView */
        'click .add': function (e) {
            const name = $(e.currentTarget).data('name');

            this.trigger('add', name);
            this.close();
        },
        /** @this AddDashletModalView */
        'keyup input[data-name="quick-search"]': function (e) {
            this.processQuickSearch(e.currentTarget.value);
        },
    }

    data() {
        return {
            dashletList: this.dashletList,
        };
    }

    setup() {
        this.headerText = this.translate('Add Dashlet');

        const dashletList = Object.keys(this.getMetadata().get('dashlets') || {})
            .sort((v1, v2) => {
                return this.translate(v1, 'dashlets').localeCompare(this.translate(v2, 'dashlets'));
            });

        this.translations = {};

        this.dashletList = dashletList.filter(item => {
            const aclScope = this.getMetadata().get(['dashlets', item, 'aclScope']) || null;
            const accessDataList = this.getMetadata().get(['dashlets', item, 'accessDataList']) || null;

            if (this.options.parentType === 'Settings') {
                return true;
            }

            if (this.options.parentType === 'Portal') {
                if (accessDataList && accessDataList.find(item => item.inPortalDisabled)) {
                    return false;
                }

                return true;
            }

            if (aclScope) {
                if (!this.getAcl().check(aclScope)) {
                    return false;
                }
            }

            if (accessDataList) {
                if (!Espo.Utils.checkAccessDataList(accessDataList, this.getAcl(), this.getUser())) {
                    return false;
                }
            }

            this.translations[item] = this.translate(item, 'dashlets');

            return true;
        });

        this.dashletList.forEach(item => {
            this.translations[item] = this.translate(item, 'dashlets');
        });
    }

    afterRender() {
        this.$noData = this.$el.find('.no-data');

        setTimeout(() => {
            this.$el.find('input[data-name="quick-search"]').focus()
        }, 100);
    }

    processQuickSearch(text) {
        text = text.trim();

        const $noData = this.$noData;

        $noData.addClass('hidden');

        if (!text) {
            this.$el.find('ul .list-group-item').removeClass('hidden');

            return;
        }

        const matchedList = [];

        const lowerCaseText = text.toLowerCase();

        this.dashletList.forEach(item => {
            if (!(item in this.translations)) {
                return;
            }

            const label = this.translations[item].toLowerCase();

            for (const word of label.split(' ')) {
                const matched = word.indexOf(lowerCaseText) === 0;

                if (matched) {
                    matchedList.push(item);

                    return;
                }
            }
        });

        if (matchedList.length === 0) {
            this.$el.find('ul .list-group-item').addClass('hidden');

            $noData.removeClass('hidden');

            return;
        }

        this.dashletList.forEach(item => {
            const $row = this.$el.find(`ul .list-group-item[data-name="${item}"]`);

            if (!~matchedList.indexOf(item)) {
                $row.addClass('hidden');

                return;
            }

            $row.removeClass('hidden');
        });
    }
}

export default AddDashletModalView;
