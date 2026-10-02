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

export default class extends ModalView {

    template = 'admin/formula/modals/add-function'

    backdrop = true

    data() {
        let text = this.translate('formulaFunctions', 'messages', 'Admin')
            .replace('{documentationUrl}', this.documentationUrl);
        text = this.getHelper().transformMarkdownText(text, {linksInNewTab: true}).toString();

        return {
            functionDataList: this.functionDataList,
            text: text,
        };
    }

    setup() {
        this.addActionHandler('add', (e, target) => {
            this.trigger('add', target.dataset.value);
        })

        this.headerText = this.translate('Function');

        this.documentationUrl = 'https://docs.espocrm.com/administration/formula/';

        this.functionDataList = this.options.functionDataList ||
            this.getMetadata().get('app.formula.functionList') || [];
    }
}
