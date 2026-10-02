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
import Select from 'ui/select';

export default class extends BaseFieldView {

    detailTemplate = 'email/fields/email-address-varchar/detail'
    editTemplate = 'email/fields/compose-from-address/edit'

    data() {
        let noSmtpMessage = this.translate('noSmtpSetup', 'messages', 'Email');

        const linkHtml = $('<a>')
            .attr('href', '#EmailAccount')
            .text(this.translate('EmailAccount', 'scopeNamesPlural'))
            .get(0).outerHTML;

        noSmtpMessage = noSmtpMessage.replace('{link}', linkHtml);

        return {
            list: this.list,
            noSmtpMessage: noSmtpMessage,
            ...super.data(),
        };
    }

    setup() {
        super.setup();

        this.nameHash = {...(this.model.get('nameHash') || {})};
        this.typeHash = this.model.get('typeHash') || {};
        this.idHash = this.model.get('idHash') || {};

        this.list = this.getUser().get('emailAddressList') || [];
    }

    afterRenderEdit() {
        if (this.$element.length) {
            Select.init(this.$element);
        }
    }

    getValueForDisplay() {
        if (this.isDetailMode()) {
            const address = this.model.get(this.name);

            return this.getDetailAddressHtml(address);
        }

        return super.getValueForDisplay();
    }

    getDetailAddressHtml(address) {
        if (!address) {
            return '';
        }

        const name = this.nameHash[address] || null;

        const entityType = this.typeHash[address] || null;
        const id = this.idHash[address] || null;

        if (id && name) {
            return $('<div>')
                .append(
                    $('<a>')
                        .attr('href', `#${entityType}/view/${id}`)
                        .attr('data-scope', entityType)
                        .attr('data-id', id)
                        .text(name),
                    ' ',
                    $('<span>').addClass('text-muted chevron-right'),
                    ' ',
                    $('<span>').text(address)
                )
                .get(0).outerHTML;
        }

        const $div = $('<div>');

        if (name) {
            $div.append(
                $('<span>')
                    .addClass('email-address-line')
                    .text(name)
                    .append(
                        ' ',
                        $('<span>').addClass('text-muted chevron-right'),
                        ' ',
                        $('<span>').text(address)
                    )
            );

            return $div.get(0).outerHTML;
        }

        $div.append(
            $('<span>')
                .addClass('email-address-line')
                .text(address)
        )

        return $div.get(0).outerHTML;
    }
}
