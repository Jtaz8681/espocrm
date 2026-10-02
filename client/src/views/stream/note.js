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

class NoteStreamView extends View {

    /**
     * @protected
     * @type {string|null}
     */
    messageName = null

    /**
     * @protected
     * @type {string|null}
     */
    messageTemplate = null

    /**
     * Data to pass to a message template.
     *
     * @protected
     * @type {Object.<string,JQuery|Element|string>|null}
     */
    messageData = null

    /** @protected */
    isEditable = false
    /** @protected */
    isRemovable = false
    /** @protected */
    isSystemAvatar = false

    rowActionsView = 'views/stream/record/row-actions/default'

    data() {
        return {
            isUserStream: this.isUserStream,
            noEdit: this.options.noEdit,
            acl: this.options.acl,
            onlyContent: this.options.onlyContent,
            avatar: this.getAvatarHtml(),
        };
    }

    init() {
        this.createField('createdAt', null, null, 'views/fields/datetime-short');

        /** @type {string} */
        this.listType = this.options.listType;

        this.isUserStream = this.options.isUserStream;
        this.isThis = !this.isUserStream;

        this.parentModel = this.options.parentModel;

        if (!this.isUserStream) {
            if (this.parentModel) {
                if (
                    this.parentModel.entityType !== this.model.get('parentType') ||
                    this.parentModel.id !== this.model.get('parentId')
                ) {
                    this.isThis = false;
                }
            }
        }

        if (this.getUser().isAdmin()) {
            this.isRemovable = true;
        }

        if (this.messageName && this.isThis) {
            this.messageName += 'This';
        }

        if (!this.isThis) {
            this.createField('parent');
        }

        const translatedEntityType = this.translateEntityType(this.model.get('parentType'));

        this.messageData = {
            'user': 'field:createdBy',
            'entity': 'field:parent',
            'entityType': translatedEntityType,
        };

        const rowActionsView = this.options.rowActionsView || this.rowActionsView;

        if (!this.options.noEdit && (this.isEditable || this.isRemovable)) {
            this.createView('right', rowActionsView, {
                selector: '.right-container',
                acl: this.options.acl,
                model: this.model,
                isEditable: this.isEditable,
                isRemovable: this.isRemovable,
                listType: this.listType,
                isThis: this.isThis,
                parentModel: this.parentModel,
                isNotification: this.options.isNotification,
            });
        }
    }

    translateEntityType(entityType, isPlural) {
        let string = isPlural ?
            (this.translate(entityType, 'scopeNamesPlural') || '') :
            (this.translate(entityType, 'scopeNames') || '');

        if (!this.isToUpperCaseStringItems()) {
            string = string.toLowerCase();
        }

        return string;
    }

    isToUpperCaseStringItems() {
        const language = this.getPreferences().get('language') || this.getConfig().get('language');

        return ['de_DE', 'nl_NL'].includes(language);
    }

    createField(name, type, params, view, options) {
        type = type || this.model.getFieldType(name) || 'base';

        const o = {
            model: this.model,
            defs: {
                name: name,
                params: params || {}
            },
            selector: '.cell-' + name,
            mode: 'list',
        };

        if (options) {
            for (const i in options) {
                o[i] = options[i];
            }
        }

        this.createView(name, view || this.getFieldManager().getViewName(type), o);
    }

    isMale() {
        return this.model.get('createdByGender') === 'Male';
    }

    isFemale() {
        return this.model.get('createdByGender') === 'Female';
    }

    createMessage() {
        if (!this.messageTemplate) {
            let isTranslated = false;
            const parentType = this.model.get('parentType') || null;

            if (this.isMale()) {
                this.messageTemplate = this.translate(this.messageName, 'streamMessagesMale', parentType) || '';

                if (this.messageTemplate !== this.messageName) {
                    isTranslated = true;
                }
            } else if (this.isFemale()) {
                this.messageTemplate = this.translate(this.messageName, 'streamMessagesFemale', parentType) || '';

                if (this.messageTemplate !== this.messageName) {
                    isTranslated = true;
                }
            }

            if (!isTranslated) {
                this.messageTemplate = this.translate(this.messageName, 'streamMessages', parentType) || '';
            }
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
        let id = this.model.get('createdById');

        if (this.isSystemAvatar) {
            id = this.getHelper().getAppParam('systemUserId');
        }

        return this.getHelper().getAvatarHtml(id, 'small', 20);
    }

    /**
     *
     * @param [scope]
     * @param [id]
     * @return {string|null}
     */
    getIconHtml(scope, id) {
        if (!scope) {
            if (!this.model.attributes.parentType) {
                return null;
            }

            scope = this.model.attributes.parentType;
            id = this.model.attributes.parentId;
        }

        if (this.options.isInGroup) {
            return null;
        }

        if (this.isThis && this.parentModel && scope === this.parentModel.entityType) {
            return null;
        }

        const iconClass = this.getMetadata().get(`clientDefs.${scope}.iconClass`);
        const color = this.getMetadata().get(`clientDefs.${scope}.color`);

        if (!iconClass) {
            return null;
        }

        return $('<span>')
            .addClass(iconClass)
            .addClass('action text-muted icon')
            .css('cursor', 'pointer')
            .css('color', color ? color : '')
            .attr('title', this.translate('View'))
            .attr('data-action', 'quickView')
            .attr('data-id', id)
            .attr('data-scope', scope)
            .get(0).outerHTML;
    }
}

export default NoteStreamView;
