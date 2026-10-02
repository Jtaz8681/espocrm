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

import VarcharFieldView from 'views/fields/varchar';

// noinspection JSUnusedGlobalSymbols
export default class extends VarcharFieldView {

    detailTemplateContent = `
        {{#if isNotEmpty}}
            <a
                role="button"
                data-action="copyToClipboard"
                class="pull-right text-soft"
                title="{{translate 'Copy to Clipboard'}}"
            ><span class="far fa-copy"></span></a>
            {{value}}
        {{else}}
            <span class="none-value">{{translate 'None'}}</span>
        {{/if}}
    `

    /**
     * @private
     * @type {import('collection').default|null}
     */
    portalCollection = null

    data() {
        const isNotEmpty = this.model.entityType !== 'AuthenticationProvider' ||
            (this.portalCollection && this.portalCollection.length);

        return {
            value: this.getValueForDisplay(),
            isNotEmpty: isNotEmpty,
        };
    }

    /**
     * @protected
     */
    copyToClipboard() {
        const value = this.getValueForDisplay();

        navigator.clipboard.writeText(value).then(() => {
            Espo.Ui.success(this.translate('Copied to clipboard'));
        });
    }

    getValueForDisplay() {
        if (this.model.entityType === 'AuthenticationProvider') {
            if (!this.portalCollection) {
                return null;
            }

            return this.portalCollection.models
                .map(model => {
                    const file = 'oauth-callback.php'
                    const url = (model.get('url') || '').replace(/\/+$/, '') + `/${file}`;

                    const checkPart = `/portal/${model.id}/${file}`;

                    if (!url.endsWith(checkPart)) {
                        return url;
                    }

                    return url.slice(0, - checkPart.length) + `/portal/${file}`;
                })
                .join('\n');
        }

        const siteUrl = (this.getConfig().get('siteUrl') || '').replace(/\/+$/, '');

        return siteUrl + '/oauth-callback.php';
    }

    setup() {
        super.setup();

        if (this.model.entityType === 'AuthenticationProvider') {
            this.getCollectionFactory().create('Portal')
                .then(collection => {
                    collection.data.select = ['url', 'isDefault'].join(',');

                    collection.fetch().then(() => {
                        this.portalCollection = collection;

                        this.reRender();
                    })
                });
        }
    }
}
