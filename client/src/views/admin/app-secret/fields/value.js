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

import TextFieldView from 'views/fields/text';

export default class extends TextFieldView {

    detailTemplateContent = `**********`

    validations = ['required']

    changingMode = false

    data() {
        return {
            isNew: this.model.isNew(),
            ...super.data(),
        };
    }

    afterRenderEdit() {
        super.afterRenderEdit();

        if (!this.model.isNew() && !this.changingMode) {
            this.element.innerHTML = '';

            const a = document.createElement('a');
            a.role = 'button';
            a.onclick = () => this.changePassword();
            a.textContent = this.translate('change');

            this.element.appendChild(a);
        }
    }

    onDetailModeSet() {
        this.changingMode = false;

        return super.onDetailModeSet();
    }

    fetch() {
        if (!this.model.isNew() && !this.changingMode) {
            return {};
        }

        return super.fetch();
    }

    async changePassword() {
        this.changingMode = true;

        await this.reRender();
    }
}
