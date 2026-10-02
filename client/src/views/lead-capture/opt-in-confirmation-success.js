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

import View from 'view';
import Model from 'model';

class OptInConfirmationSuccessView extends View {

    template = 'lead-capture/opt-in-confirmation-success'

    setup() {
        const model = new Model();

        this.resultData = this.options.resultData;

        if (this.resultData.message) {
            model.set('message', this.resultData.message);

            this.createView('messageField', 'views/fields/text', {
                selector: '.field[data-name="message"]',
                mode: 'detail',
                inlineEditDisabled: true,
                model: model,
                name: 'message',
            });
        }
    }

    data() {
        return {
            resultData: this.options.resultData,
            defaultMessage: this.getLanguage().translate('optInIsConfirmed', 'messages', 'LeadCapture'),
        };
    }
}

export default OptInConfirmationSuccessView;
