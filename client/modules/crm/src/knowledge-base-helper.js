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

import Ajax from 'ajax';

/**
 * @todo Move to modules/crm/helpers.
 */
class KnowledgeBaseHelper {

    /**
     * @param {module:language} language
     */
    constructor(language) {
        this.language = language;
    }

    getAttributesForEmail(model, attributes, callback) {
        attributes = attributes || {};
        attributes.body = model.get('body');

        if (attributes.name) {
            attributes.name = attributes.name + ' ';
        } else {
            attributes.name = '';
        }

        attributes.name += this.language.translate('KnowledgeBaseArticle', 'scopeNames') + ': ' +
            model.get('name');

        Ajax.postRequest('KnowledgeBaseArticle/action/getCopiedAttachments', {
            id: model.id,
            parentType: 'Email',
            field : 'attachments',
        }).then(data => {
            attributes.attachmentsIds = data.ids;
            attributes.attachmentsNames = data.names;
            attributes.isHtml = true;

            callback(attributes);
        });
    }
}

export default KnowledgeBaseHelper;
