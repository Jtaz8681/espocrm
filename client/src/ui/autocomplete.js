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
import Handlebars from 'handlebars';

/** @module ui/autocomplete */

/**
 * @typedef {Object} AutocompleteItem
 * @property {string} value
 */

/**
 * @typedef {{
 *     name?: string,
 *     forceHide?: boolean,
 *     lookup?: string[],
 *     lookupFunction?: function (string): Promise<Array<AutocompleteItem & Record>>,
 *     minChars?: Number,
 *     formatResult?: function (AutocompleteItem & Record<string, any>): string,
 *     onSelect?: function (AutocompleteItem & Record<string, any>): void,
 *     beforeRender?: function (HTMLElement): void,
 *     triggerSelectOnValidInput?: boolean,
 *     autoSelectFirst?: boolean,
 *     handleFocusMode?: 1|2|3,
 *     focusOnSelect?: boolean,
 *     catchFastEnter?: boolean,
 * }} AutocompleteOptions
 */

/**
 * An autocomplete.
 */
class Autocomplete {

    /**
     * @param {HTMLInputElement} element
     * @param {AutocompleteOptions} options
     */
    constructor(element, options) {
        /** @private */
        this.$element = $(element);

        let deferredEnter = false;
        let catchEnter = false;
        let catchEnterTimeout = null;

        this.$element.on('keydown', e => {
            if (e.code === 'Tab' && !this.$element.val()) {
                e.stopImmediatePropagation();
            }

            // Scanner input.
            if (options.catchFastEnter) {
                if (e.code !== 'Enter') {
                    catchEnter = true;

                    if (catchEnterTimeout) {
                        clearTimeout(catchEnterTimeout);
                    }

                    catchEnterTimeout = setTimeout(() => catchEnter = false, 40);
                }

                if (catchEnter && e.code === 'Enter' && this.$element.val()) {
                    deferredEnter = true;
                } else {
                    deferredEnter = false;
                }
            }
        });

        const lookup = options.lookupFunction ?
            (query, done) => {
                options.lookupFunction(query)
                    .then(items => {
                        done({suggestions: items})
                    });
            } :
            options.lookup;

        const lookupFilter = !options.lookupFunction ?
            (/** AutocompleteItem */suggestion, /** string */query, /** string */queryLowerCase) => {
                if (suggestion.value.toLowerCase().indexOf(queryLowerCase) === 0) {
                    return suggestion.value.length !== queryLowerCase.length;
                }

                return false;
            } :
            undefined;

        const $modalBody = this.$element.closest('.modal-body');

        const isModal = !!$modalBody.length;

        this.$element.autocomplete({
            beforeRender: $container => {
                if (options.beforeRender) {
                    options.beforeRender($container.get(0));
                }

                if (this.$element.hasClass('input-sm')) {
                    $container.addClass('small');
                }

                if (options.forceHide) {
                    // Prevent an issue that suggestions are shown and not hidden
                    // when clicking outside the window and then focusing back on the document.
                    if (this.$element.get(0) !== document.activeElement) {
                        setTimeout(() => this.$element.autocomplete('hide'), 30);
                    }
                }

                if (isModal) {
                    // Fixes dropdown dissapearing when clicking scrollbar.
                    $container.on('mousedown', e => {
                        e.preventDefault();
                    });
                }

                if (deferredEnter) {
                    setTimeout(() => {
                        element.dispatchEvent(
                            new KeyboardEvent("keydown", {
                                key: 'Enter',
                                code: 'Enter',
                                keyCode: 13,
                                which: 13,
                                bubbles: true,
                                cancelable: true
                            })
                        );
                    }, 100);
                }

                catchEnter = false;
                deferredEnter = false;
            },
            lookup: lookup,
            minChars: options.minChars || 0,
            noCache: true,
            autoSelectFirst: options.autoSelectFirst,
            appendTo: $modalBody.length ? $modalBody : 'body',
            forceFixPosition: true,
            maxHeight: 308,
            formatResult: item => {
                if (options.formatResult) {
                    return options.formatResult(item);
                }

                return Handlebars.Utils.escapeExpression(item.value);
            },
            lookupFilter: lookupFilter,
            onSelect: item => {
                if (options.onSelect) {
                    options.onSelect(item);
                }

                if (options.focusOnSelect) {
                    this.$element.focus();
                }

                catchEnter = false;
                deferredEnter = false;
            },
            triggerSelectOnValidInput: options.triggerSelectOnValidInput || false,
        });

        this.$element.attr('autocomplete', 'espo-' + (options.name || 'dummy'));

        if (options.handleFocusMode) {
            this.initHandleFocus(options);
        }
    }

    /**
     * @private
     * @param {AutocompleteOptions} options
     */
    initHandleFocus(options) {
        this.$element.off('focus.autocomplete');

        this.$element.on('focus', () => {
            if (options.handleFocusMode === 1) {
                if (this.$element.val()) {
                    return;
                }

                this.$element.autocomplete('onValueChange');

                return;
            }

            if (this.$element.val()) {
                // noinspection JSUnresolvedReference
                this.$element.get(0).select();

                return;
            }

            this.$element.autocomplete('onFocus');
        });

        if (options.handleFocusMode === 3) {
            this.$element.on('change', () => this.$element.val(''));
        }
    }

    /**
     * Dispose.
     */
    dispose() {
        this.$element.autocomplete('dispose');
    }

    /**
     * Hide.
     */
    hide() {
        this.$element.autocomplete('hide');
    }

    /**
     * Clear.
     */
    clear() {
        this.$element.autocomplete('clear');
    }
}

export default Autocomplete;
