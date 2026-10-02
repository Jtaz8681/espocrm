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
import EmailEmailAddressFieldView from 'views/email/fields/email-address';
import RecordModal from 'helpers/record-modal';
import EmailHelper from 'email-helper';

class EmailFromAddressVarchar extends BaseFieldView {

    // language=Handlebars
    listTemplateContent = `
        {{#if value}}{{{value}}}{{/if}}
    `

    detailTemplate = 'email/fields/email-address-varchar/detail'

    validations = ['required', 'email']
    skipCurrentInAutocomplete = true

    emailAddressRegExp = new RegExp(
        /^[-!#$%&'*+/=?^_`{|}~A-Za-z0-9]+(?:\.[-!#$%&'*+/=?^_`{|}~A-Za-z0-9]+)*/.source +
        /@([A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9][A-Za-z0-9-]*[A-Za-z0-9]/.source
    )

    setup() {
        super.setup();

        this.erasedPlaceholder = 'ERASED:';

        this.on('render', () => {
            if (this.mode === this.MODE_SEARCH) {
                return;
            }

            this.initAddressList();
        });
    }

    events = {
        /** @this EmailFromAddressVarchar */
        'click [data-action="createContact"]': function (e) {
            const address = $(e.currentTarget).data('address');

            this.createPerson('Contact', address);
        },
        /** @this EmailFromAddressVarchar */
        'click [data-action="createLead"]': function (e) {
            const address = $(e.currentTarget).data('address');

            this.createPerson('Lead', address);
        },
        /** @this EmailFromAddressVarchar */
        'click [data-action="addToContact"]': function (e) {
            const address = $(e.currentTarget).data('address');

            this.addToPerson('Contact', address);
        },
        /** @this EmailFromAddressVarchar */
        'click [data-action="addToLead"]': function (e) {
            const address = $(e.currentTarget).data('address');

            this.addToPerson('Lead', address);
        },
        /** @this EmailFromAddressVarchar */
        'auxclick a[href][data-scope][data-id]': function (e) {
            const isCombination = e.button === 1 && (e.ctrlKey || e.metaKey);

            if (!isCombination) {
                return;
            }

            const $target = $(e.currentTarget);

            const id = $target.attr('data-id');
            const scope = $target.attr('data-scope');

            e.preventDefault();
            e.stopPropagation();

            this.quickView({
                id: id,
                scope: scope,
            });
        },
    }

    // noinspection JSCheckFunctionSignatures
    data() {
        const data = super.data();

        const address = this.model.get(this.name);

        if (address && !(address in this.idHash) && this.model.get('parentId')) {
            if (this.getAcl().check('Contact', 'edit')) {
                data.showCreate = true;
            }
        }

        data.valueIsSet = this.model.has(this.name);

        return data;
    }

    afterRender() {
        super.afterRender();

        if (this.mode === this.MODE_SEARCH && this.getAcl().check('Email', 'create')) {
            EmailEmailAddressFieldView.prototype.initSearchAutocomplete.call(this);
        }

        if (this.mode === this.MODE_EDIT && this.getAcl().check('Email', 'create')) {
            EmailEmailAddressFieldView.prototype.initSearchAutocomplete.call(this);
        }

        if (this.mode === this.MODE_SEARCH) {
            this.$input.on('input', () => {
                this.trigger('change');
            });
        }
    }

    // noinspection JSUnusedGlobalSymbols
    getAutocompleteMaxCount() {
        return EmailEmailAddressFieldView.prototype.getAutocompleteMaxCount.call(this);
    }

    initAddressList() {
        this.nameHash = {};
        this.typeHash = this.model.get('typeHash') || {};
        this.idHash = this.model.get('idHash') || {};

        _.extend(this.nameHash, this.model.get('nameHash') || {});
    }

    getAttributeList() {
        const list = super.getAttributeList();

        list.push('nameHash');
        list.push('idHash');
        list.push('accountId');

        return list;
    }

    getValueForDisplay() {
        if (this.mode === this.MODE_DETAIL || this.mode === this.MODE_LIST) {
            const address = this.model.get(this.name);

            return this.getDetailAddressHtml(address);
        }

        return super.getValueForDisplay();
    }

    /**
     * @protected
     * @param {string} address
     * @return {string}
     */
    getDetailAddressHtml(address) {
        if (!address) {
            return '';
        }

        const fromString = this.model.get('fromString') || this.model.get('fromName');

        const name = this.nameHash[address] || this.parseNameFromStringAddress(fromString) || null;

        const entityType = this.typeHash[address] || null;
        const id = this.idHash[address] || null;

        if (id) {
            let avatarHtml = '';

            if (entityType === 'User') {
                const size = this.mode === this.MODE_DETAIL ? 18 : 16;

                avatarHtml = this.getHelper().getAvatarHtml(id, 'small', size, 'avatar-link');
            } else if (entityType) {
                avatarHtml = this.getHelper().getScopeColorIconHtml(entityType);
            }

            const title = this.mode === this.MODE_LIST ? name : null;
            const className = this.mode === this.MODE_LIST ? 'text-default' : 'text-record';

            const $item = $('<div class="email-address-detail-item">')
                .append(
                    avatarHtml,
                    $('<a>')
                        .attr('href', `#${entityType}/view/${id}`)
                        .attr('data-scope', entityType)
                        .attr('data-id', id)
                        .attr('title', title)
                        .addClass(className)
                        .text(name),
                );

            if (this.mode === this.MODE_DETAIL) {
                $item.append(
                    ' ',
                    $('<span>').addClass('text-muted middle-dot'),
                    ' ',
                    $('<span>').text(address)
                );
            }

            return $item.get(0).outerHTML;
        }

        const $div = $('<div>');
        $div.addClass('email-address-lines-container')

        if (
            this.mode !== this.MODE_LIST &&
            (this.getAcl().check('Contact', 'create') || this.getAcl().check('Lead', 'create'))
        ) {
            $div.append(
                this.getCreateHtml(address)
            );
        }

        if (name) {
            const $span = $('<span>')
                .addClass('email-address-line')
                .text(name);

            if (this.mode === this.MODE_DETAIL) {
                $span.append(
                    ' ',
                    $('<span>').addClass('text-muted middle-dot'),
                    ' ',
                    $('<span>').text(address),
                );
            }

            $div.append($span);

            return $div.get(0).outerHTML;
        }

        $div.append(
            $('<span>')
                .addClass('email-address-line')
                .text(address)
        )

        return $div.get(0).outerHTML;
    }

    getCreateHtml(address) {
        const $ul = $('<ul>')
            .addClass('dropdown-menu')
            .attr('role', 'menu');

        const $container = $('<span>')
            .addClass('dropdown email-address-create-dropdown pull-right')
            .append(
                $('<button>')
                    .addClass('dropdown-toggle btn btn-link btn-sm')
                    .attr('data-toggle', 'dropdown')
                    .append(
                        $('<span>').addClass('caret text-muted')
                    ),
                $ul
            );

        if (this.getAcl().check('Contact', 'create')) {
            $ul.append(
                $('<li>')
                    .append(
                        $('<a>')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('data-action', 'createContact')
                            .attr('data-address', address)
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-icon fas fa-plus';
                                    return span;
                                })()
                            )
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-text';
                                    span.textContent = this.translate('Create Contact', 'labels', 'Email');
                                    return span;
                                })()
                            )
                    )
            );
        }

        if (this.getAcl().check('Lead', 'create')) {
            $ul.append(
                $('<li>')
                    .append(
                        $('<a>')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('data-action', 'createLead')
                            .attr('data-address', address)
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-icon fas fa-plus';
                                    return span;
                                })()
                            )
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-text';
                                    span.textContent = this.translate('Create Lead', 'labels', 'Email');
                                    return span;

                                })()
                            )
                    )
            );
        }

        if (this.getAcl().check('Contact', 'edit')) {
            $ul.append(
                $('<li>')
                    .append(
                        $('<a>')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('data-action', 'addToContact')
                            .attr('data-address', address)
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-icon far fa-square-plus';
                                    return span;
                                })()
                            )
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-text';
                                    span.textContent = this.translate('Add to Contact', 'labels', 'Email');
                                    return span;
                                })()
                            )
                    )
            );
        }

        if (this.getAcl().check('Lead', 'edit')) {
            $ul.append(
                $('<li>')
                    .append(
                        $('<a>')
                            .attr('role', 'button')
                            .attr('tabindex', '0')
                            .attr('data-action', 'addToLead')
                            .attr('data-address', address)
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-icon far fa-square-plus';
                                    return span;
                                })()
                            )
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-text';
                                    span.textContent = this.translate('Add to Lead', 'labels', 'Email');
                                    return span;
                                })()
                            )
                    )
            );
        }

        if (this.name === 'from' && this.getAcl().check('EmailFilter', 'create')) {
            if ($ul.children().length) {
                $ul.append(`<li class="divider"></li>`)
            }

            const url = '#EmailFilter/create?from=' + encodeURI(address) +
                '&returnUrl=' + encodeURI(this.getRouter().getCurrentUrl());

            $ul.append(
                $('<li>')
                    .append(
                        $('<a>')
                            .attr('tabindex', '0')
                            .attr('href', url)
                            /*.append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-icon fas fa-plus';
                                    return span;
                                })()
                            )*/
                            .append(
                                (() => {
                                    const span = document.createElement('span');
                                    span.className = 'item-text';
                                    span.textContent = this.translate('Create EmailFilter', 'labels', 'EmailFilter');
                                    return span;
                                })()
                            )
                    )
            );
        }

        return $container.get(0).outerHTML;
    }

    /**
     * @param {string} value
     * @return {string|null}
     */
    parseNameFromStringAddress(value) {
        value = value || '';

        const emailHelper = new EmailHelper();

        return emailHelper.parseNameFromStringAddress(value);
    }

    /**
     * @internal Called with a different context from another view.
     * @param {string} scope
     * @param {string} address
     */
    createPerson(scope, address) {
        const fromString = this.model.get('fromString') || this.model.get('fromName');

        /** @type {string|null} */
        let name = this.nameHash[address] || null;

        if (!name && this.name === 'from' && fromString) {
            const emailHelper = new EmailHelper();

            name = emailHelper.parseNameFromStringAddress(fromString);
        }

        if (name) {
            name = EmailFromAddressVarchar.stripQuotesFromName(name);
        }

        const attributes = {
            emailAddress: address,
        };

        if (this.model.get('accountId') && scope === 'Contact') {
            attributes.accountId = this.model.get('accountId');
            attributes.accountName = this.model.get('accountName');
        }

        if (name) {
            const firstName = name.split(' ').slice(0, -1).join(' ');
            const lastName = name.split(' ').slice(-1).join(' ');

            attributes.firstName = firstName;
            attributes.lastName = lastName;
        }

        const helper = new RecordModal();

        helper.showCreate(this, {
            entityType: scope,
            attributes: attributes,
            afterSave: model => {
                const nameHash = Espo.Utils.clone(this.model.get('nameHash') || {});
                const typeHash = Espo.Utils.clone(this.model.get('typeHash') || {});
                const idHash = Espo.Utils.clone(this.model.get('idHash') || {});

                idHash[address] = model.id;
                nameHash[address] = model.attributes.name;
                typeHash[address] = scope;

                this.idHash = idHash;
                this.nameHash = nameHash;
                this.typeHash = typeHash;

                const attributes = {
                    nameHash: nameHash,
                    idHash: idHash,
                    typeHash: typeHash,
                };

                setTimeout(() => {
                    this.model.set(attributes);

                    if (this.model.attributes.icsContents) {
                        this.model.fetch();
                    }
                }, 50);
            },
        });
    }

    /**
     * @internal Called with a different context from another view.
     * @param {string} scope
     * @param {string} address
     */
    async addToPerson(scope, address) {
        const fromString = this.model.get('fromString') || this.model.get('fromName');
        let name = this.nameHash[address] || null;

        if (!name && this.name === 'from' && fromString) {
            const emailHelper = new EmailHelper();

            name = emailHelper.parseNameFromStringAddress(fromString);
        }

        if (name) {
            name = EmailFromAddressVarchar.stripQuotesFromName(name);
        }

        const attributes = {
            emailAddress: address,
        };

        if (this.model.get('accountId') && scope === 'Contact') {
            attributes.accountId = this.model.get('accountId');
            attributes.accountName = this.model.get('accountName');
        }

        const filters = {};

        if (name) {
            filters['name'] = {
                type: 'equals',
                field: 'name',
                value: name,
            };
        }

        const afterSave = /** import('model').default */model => {
            const nameHash = Espo.Utils.clone(this.model.get('nameHash') || {});
            const typeHash = Espo.Utils.clone(this.model.get('typeHash') || {});
            const idHash = Espo.Utils.clone(this.model.get('idHash') || {});

            idHash[address] = model.id;
            nameHash[address] = model.attributes.name;
            typeHash[address] = scope;

            this.idHash = idHash;
            this.nameHash = nameHash;
            this.typeHash = typeHash;

            const attributes = {
                nameHash: nameHash,
                idHash: idHash,
                typeHash: typeHash,
            };

            setTimeout(() => {
                this.model.set(attributes);

                if (this.model.attributes.icsContents) {
                    this.model.fetch();
                }
            }, 50);
        };

        const viewName = this.getMetadata().get(`clientDefs.${scope}.modalViews.select`) ||
            'views/modals/select-records';

        /** @type {module:views/modals/select-records~Options} */
        const options = {
            entityType: scope,
            createButton: false,
            filters: filters,
            onSelect: async models => {
                const model = models[0];

                if (!model.attributes.emailAddress) {
                    await model.save({emailAddress: address}, {patch: true});

                    afterSave(model);

                    return;
                }

                await model.fetch();

                const emailAddressData = [...(model.attributes.emailAddressData || [])];

                emailAddressData.push({
                    emailAddress: address,
                    primary: emailAddressData.length === 0
                });

                await model.save({emailAddressData: emailAddressData}, {patch: true});

                afterSave(model);
            }
        };

        Espo.Ui.notifyWait();

        const view = await this.createView('modal', viewName, options);

        await view.render();

        Espo.Ui.notify();
    }

    /**
     * @private
     * @param {string} name
     * @return {string}
     */
    static stripQuotesFromName(name) {
        if (name && /^(['"]).*\1$/.test(name)) {
            name = name.substring(1, name.length - 1);
        }

        return name;
    }

    fetchSearch() {
        const value = this.$element.val().trim();

        if (value) {
            return {
                type: 'equals',
                value: value,
            }
        }

        return null;
    }

    // noinspection JSUnusedGlobalSymbols
    validateEmail() {
        const address = this.model.get(this.name);

        if (!address) {
            return;
        }

        const addressLowerCase = String(address).toLowerCase();

        if (!this.emailAddressRegExp.test(addressLowerCase) && address.indexOf(this.erasedPlaceholder) !== 0) {
            const msg = this.translate('fieldShouldBeEmail', 'messages')
                .replace('{field}', this.getLabelText());

            this.showValidationMessage(msg);

            return true;
        }
    }

    quickView(data) {
        const helper = new RecordModal();

        helper.showDetail(this, {
            id: data.id,
            entityType: data.scope,
        });
    }
}

export default EmailFromAddressVarchar;
