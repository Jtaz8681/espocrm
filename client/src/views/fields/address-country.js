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

import VarcharFieldView from 'views/fields/varchar';

class AddressCountryFieldView extends VarcharFieldView {

    setupOptions() {
        const countryList = this.getCountryList();

        if (countryList.length) {
            this.params.options = Espo.Utils.clone(countryList);
        }
    }

    /**
     * @private
     * @return {string[]}
     */
    getCountryList() {
        const list = (this.getHelper().getAppParam('addressCountryData') || {}).list || [];

        if (list.length) {
            return list;
        }

        return [];
    }

    getAutocompleteLookupFunction() {
        // noinspection JSUnresolvedReference
        const list = (this.getHelper().getAppParam('addressCountryData') || {}).preferredList || [];

        if (!list.length) {
            return undefined;
        }

        const fullList = (this.params.options || []);

        return query => {
            if (query.length === 0) {
                const result = list.map(item => ({value: item}));

                return Promise.resolve(result);
            }

            const queryLowerCase = query.toLowerCase();

            const result = fullList
                .filter(item => {
                    if (item.toLowerCase().indexOf(queryLowerCase) === 0) {
                        return item.length !== queryLowerCase.length;
                    }
                })
                .map(item => ({value: item}));

            return Promise.resolve(result);
        };
    }
}

export default AddressCountryFieldView;
