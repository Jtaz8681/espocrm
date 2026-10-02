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

/** @module views/notification/items/base */

import View from 'view';

class BaseNotificationItemView extends View {

    /** @type {string} */
    messageName
    /** @type {string} */
    messageTemplate
    messageData = null
    isSystemAvatar = false

    data() {
        return {
            avatar: this.getAvatarHtml(),
        };
    }

    init() {
        this.createField('createdAt', null, null, 'views/fields/datetime-short');

        this.messageData = {};
    }

    createField(name, type, params, view) {
        type = type || this.model.getFieldType(name) || 'base';

        this.createView(name, view || this.getFieldManager().getViewName(type), {
            model: this.model,
            defs: {
                name: name,
                params: params || {}
            },
            selector: '.cell-' + name,
            mode: 'list',
        });
    }

    createMessage() {
        const parentType = this.model.get('relatedParentType') || null;

        if (!this.messageTemplate && this.messageName) {
            this.messageTemplate = this.translate(this.messageName, 'notificationMessages', parentType) || '';
        }

        if (
            this.messageTemplate.indexOf('{entityType}') === 0 &&
            typeof this.messageData.entityType === 'string'
        ) {
            this.messageData.entityTypeUcFirst = Espo.Utils.upperCaseFirst(this.messageData.entityType);

            this.messageTemplate = this.messageTemplate.replace('{entityType}', '{entityTypeUcFirst}');
        }

        this.createView('message', 'views/stream/message', {
            messageTemplate: this.messageTemplate,
            selector: '.message',
            model: this.model,
            messageData: this.messageData,
        });
    }

    getAvatarHtml() {
        let id = this.userId;

        if (this.isSystemAvatar || !id) {
            id = this.getHelper().getAppParam('systemUserId');
        }

        return this.getHelper().getAvatarHtml(id, 'small', 20);
    }

    /**
     * @param {string} entityType
     * @param {boolean} [isPlural]
     * @return {string}
     */
    translateEntityType(entityType, isPlural) {
        let string = isPlural ?
            (this.translate(entityType, 'scopeNamesPlural') || '') :
            (this.translate(entityType, 'scopeNames') || '');

        string = string.toLowerCase();

        if (this.toUpperCaseFirstLetter()) {
            string = Espo.Utils.upperCaseFirst(string);
        }

        return string;
    }

    /**
     * @property
     * @return {boolean}
     */
    toUpperCaseFirstLetter() {
        const language = this.getPreferences().get('language') || this.getConfig().get('language');

        return ['de_DE', 'nl_NL'].includes(language);
    }

    /**
     * @protected
     * @param entityType
     * @param id
     * @return {string|null}
     */
    getIconHtml(entityType, id) {
        const iconClass = this.getMetadata().get(`clientDefs.${entityType}.iconClass`);
        const color = this.getMetadata().get(`clientDefs.${entityType}.color`);

        if (!iconClass) {
            return null;
        }

        const span = document.createElement('span');
        span.className = `action text-muted icon ${iconClass}`;
        span.style.cursor = 'pointer';
        span.style.color = color ? color : '';
        span.title =  this.translate('View');
        span.dataset.action = 'quickView';
        span.dataset.id = id;
        span.dataset.scope = entityType;

        return span.outerHTML;
    }
}

export default BaseNotificationItemView;
