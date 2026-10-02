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

import {inject} from 'di';
import Settings from 'models/settings';
import Preferences from 'models/preferences';
import AclManager from 'acl-manager';

class MailtoHelper {

    /**
     * @type {Settings}
     */
    @inject(Settings)
    config

    /**
     * @type {Preferences}
     */
    @inject(Preferences)
    preferences

    /**
     * @type {AclManager}
     */
    @inject(AclManager)
    acl

    /**
     * Whether mailto should be used.
     *
     * @return {boolean}
     */
    toUse() {
        return this.config.get('emailForceUseExternalClient') ||
            this.preferences.get('emailUseExternalClient') ||
            !this.acl.checkScope('Email', 'create');
    }

    /**
     * Compose a mailto link.
     *
     * @param {Record} attributes
     * @return {string}
     */
    composeLink(attributes) {
        let link = 'mailto:';

        link += (attributes.to || '').split(';').join(',');

        const params = {};

        if (attributes.cc) {
            params.cc = attributes.cc.split(';').join(',');
        }

        let bcc = this.config.get('outboundEmailBccAddress');

        if (attributes.bcc) {
            if (!bcc) {
                bcc = '';
            } else {
                bcc += ';';
            }

            bcc += attributes.bcc;
        }

        if (bcc) {
            params.bcc = bcc.split(';').join(',');
        }

        if (attributes.name) {
            params.subject = attributes.name;
        }

        if (attributes.body) {
            params.body = /** @type {string} */attributes.body;

            if (attributes.isHtml) {
                params.body = this.htmlToPlain(params.body);
            }

            if (params.body.length > 700) {
                params.body = params.body.substring(0, 700) + '...';
            }
        }

        if (attributes.inReplyTo) {
            params['In-Reply-To'] = attributes.inReplyTo;
        }

        let part = '';

        for (const key in params) {
            if (part !== '') {
                part += '&';
            }
            else {
                part += '?';
            }

            part += key + '=' + encodeURIComponent(params[key]);
        }

        link += part;

        return link;
    }

    /**
     * @private
     * @param {string} text
     * @returns {string}
     */
    htmlToPlain(text) {
        text = text || '';

        let value = text.replace(/<br\s*\/?>/mg, '\n');

        value = value.replace(/<\/p\s*\/?>/mg, '\n\n');

        const $div = $('<div>').html(value);

        $div.find('style').remove();
        $div.find('link[ref="stylesheet"]').remove();

        value =  $div.text();

        return value;
    }
}

export default MailtoHelper;
