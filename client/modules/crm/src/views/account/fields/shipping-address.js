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

import AddressFieldView from 'views/fields/address';

export default class extends AddressFieldView {

    copyFrom = 'billingAddress'

    setup() {
        super.setup();

        this.addActionHandler('copyFromBilling', () => this.copy());

        this.attributePartList = this.getMetadata().get(['fields', 'address', 'actualFields']) || [];

        this.allAddressAttributeList = [];

        this.attributePartList.forEach(part => {
            this.allAddressAttributeList.push(this.copyFrom + Espo.Utils.upperCaseFirst(part));
            this.allAddressAttributeList.push(this.name + Espo.Utils.upperCaseFirst(part));
        });

        this.listenTo(this.model, 'change', () => {
            let isChanged = false;

            for (const attribute of this.allAddressAttributeList) {
                if (this.model.hasChanged(attribute)) {
                    isChanged = true;

                    break;
                }
            }

            if (!isChanged) {
                return;
            }

            if (!this.isEditMode() || !this.isRendered() || !this.copyButtonElement) {
                return;
            }

            if (this.toShowCopyButton()) {
                this.copyButtonElement.classList.remove('hidden');
            } else {
                this.copyButtonElement.classList.add('hidden');
            }
        });
    }

    afterRender() {
        super.afterRender();

        if (this.mode === this.MODE_EDIT && this.element) {
            const label = this.translate('Copy Billing', 'labels', 'Account');

            const button = this.copyButtonElement = document.createElement('button');

            button.classList.add('btn', 'btn-default', 'btn-sm', 'action');
            button.textContent = label;
            button.setAttribute('data-action', 'copyFromBilling')

            if (!this.toShowCopyButton()) {
                button.classList.add('hidden');
            }

            this.element.append(button);
        }
    }

    /**
     * @private
     */
    copy() {
        const fieldFrom = this.copyFrom;

        Object.keys(this.getMetadata().get('fields.address.fields') || {})
            .forEach(attr => {
                const destField = this.name + Espo.Utils.upperCaseFirst(attr);
                const sourceField = fieldFrom + Espo.Utils.upperCaseFirst(attr);

                this.model.set(destField, this.model.get(sourceField));
            });
    }

    /**
     * @private
     * @return {boolean}
     */
    toShowCopyButton() {
        let billingIsNotEmpty = false;
        let shippingIsNotEmpty = false;

        this.attributePartList.forEach(part => {
            const attribute1 = this.copyFrom + Espo.Utils.upperCaseFirst(part);

            if (this.model.get(attribute1)) {
                billingIsNotEmpty = true;
            }

            const attribute2 = this.name + Espo.Utils.upperCaseFirst(part);

            if (this.model.get(attribute2)) {
                shippingIsNotEmpty = true;
            }
        });

        return billingIsNotEmpty && !shippingIsNotEmpty;
    }
}
