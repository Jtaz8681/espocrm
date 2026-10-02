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

import BaseFieldView from 'views/fields/base';
import Autocomplete from 'ui/autocomplete';

class EmailEmailAddressFieldView extends BaseFieldView {

    getAutocompleteMaxCount() {
        if (this.autocompleteMaxCount) {
            return this.autocompleteMaxCount;
        }

        return this.getConfig().get('recordsPerPage');
    }

    afterRender() {
        super.afterRender();

        this.$input = this.$el.find('input');

        if (this.mode === this.MODE_SEARCH && this.getAcl().check('Email', 'create')) {
            this.initSearchAutocomplete();
        }

        if (this.mode === this.MODE_SEARCH) {
            this.$input.on('input', () => {
                this.trigger('change');
            });
        }
    }

    initSearchAutocomplete() {
        this.$input = this.$input || this.$el.find('input');

        /** @type {module:ajax.AjaxPromise & Promise<any>} */
        let lastAjaxPromise;

        const autocomplete = new Autocomplete(this.$input.get(0), {
            name: this.name,
            autoSelectFirst: true,
            triggerSelectOnValidInput: true,
            focusOnSelect: true,
            minChars: 1,
            forceHide: true,
            handleFocusMode: 2,
            onSelect: item => {
                this.$input.val(item.emailAddress);
            },
            formatResult: item => {
                return this.getHelper().escapeString(item.name) + ' &#60;' +
                    this.getHelper().escapeString(item.id) + '&#62;';
            },
            lookupFunction: query => {
                if (lastAjaxPromise && lastAjaxPromise.getReadyState() < 4) {
                    lastAjaxPromise.abort();
                }

                lastAjaxPromise = Espo.Ajax
                    .getRequest('EmailAddress/search', {
                        q: query,
                        maxSize: this.getAutocompleteMaxCount(),
                    });

                return lastAjaxPromise.then(/** Record[] */response => {
                    let result = response.map(item => {
                        return {
                            id: item.emailAddress,
                            name: item.entityName,
                            emailAddress: item.emailAddress,
                            entityId: item.entityId,
                            entityName: item.entityName,
                            entityType: item.entityType,
                            data: item.emailAddress,
                            value: item.emailAddress,
                        };
                    });

                    if (this.skipCurrentInAutocomplete) {
                        const current = this.$input.val();

                        result = result.filter(item => item.emailAddress !== current)
                    }

                    return result;
                });
            },
        });

        this.once('render remove', () => autocomplete.dispose());
    }

    fetchSearch() {
        let value = this.$element.val();

        if (typeof value.trim === 'function') {
            value = value.trim();
        }

        if (value) {
            return {
                type: 'equals',
                value: value,
            };
        }

        return null;
    }
}

export default EmailEmailAddressFieldView;
