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

import LinkFieldView from 'views/fields/link';

class LinkOneFieldView extends LinkFieldView {

    searchTypeList = ['is', 'isEmpty', 'isNotEmpty', 'isOneOf']

    fetchSearch() {
        const type = this.$el.find('select.search-type').val();
        const value = this.$el.find('[data-name="' + this.idName + '"]').val();

        if (['isOneOf'].includes(type) && !this.searchData.oneOfIdList.length) {
            return {
                type: 'isNotNull',
                attribute: 'id',
                data: {
                    type: type,
                },
            };
        }

        if (type === 'isOneOf') {
            if (!value) {
                return false;
            }

            return  {
                type: 'linkedWith',
                field: this.name,
                value: this.searchData.oneOfIdList,
                data: {
                    type: type,
                    oneOfIdList: this.searchData.oneOfIdList,
                    oneOfNameHash: this.searchData.oneOfNameHash,
                },
            };
        }

        if (type === 'is' || !type) {
            if (!value) {
                return false;
            }

            return  {
                type: 'linkedWith',
                field: this.name,
                value: value,
                data: {
                    type: type,
                    nameValue: this.$el.find('[data-name="' + this.nameName + '"]').val(),
                },
            };
        }

        if (type === 'isEmpty') {
            return  {
                type: 'isNotLinked',
                data: {
                    type: type,
                },
            };
        }

        if (type === 'isNotEmpty') {
            return  {
                type: 'isLinked',
                data: {
                    type: type,
                },
            };
        }
    }
}

export default LinkOneFieldView;
