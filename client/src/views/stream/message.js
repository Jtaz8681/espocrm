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

class MessageStreamView extends View {

    data() {
        return this.dataForTemplate;
    }

    setup() {
        let template = this.options.messageTemplate;
        const data = Espo.Utils.clone(this.options.messageData || {});

        this.dataForTemplate = {};

        for (let key in data) {
            const value = data[key] || '';

            if (key.indexOf('html:') === 0) {
                key = key.substring(5);
                this.dataForTemplate[key] = value;
                template = template.replace('{' + key + '}', '{{{' + key + '}}}');

                continue;
            }

            if (value instanceof jQuery) {
                this.dataForTemplate[key] = value.get(0).outerHTML;
                template = template.replace('{' + key + '}', '{{{' + key + '}}}');

                continue;
            }

            if (value instanceof Element) {
                this.dataForTemplate[key] = value.outerHTML;
                template = template.replace('{' + key + '}', '{{{' + key + '}}}');

                continue;
            }

            if (!value.indexOf) {
                continue;
            }

            if (value.indexOf('field:') === 0) {
                const field = value.substring(6);
                this.createField(key, field);

                const keyEscaped = this.getHelper().escapeString(key);

                template = template.replace(
                    '{' + key + '}',
                    `<span data-key="${keyEscaped}">\{\{\{${key}\}\}\}</span>`
                );

                continue;
            }

            this.dataForTemplate[key] = value;
            template = template.replace(`{${key}}`, `{{${key}}}`);
        }

        this.templateContent = template;
    }

    createField(key, name, type, params) {
        type = type || this.model.getFieldType(name) || 'base';

        this.createView(key, this.getFieldManager().getViewName(type), {
            model: this.model,
            defs: {
                name: name,
                params: params || {}
            },
            mode: 'detail',
            readOnly: true,
            selector: `[data-key="${key}"]`,
        });
    }
}

export default MessageStreamView;
