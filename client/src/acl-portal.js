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

/** @module acl-portal */

import Acl from 'acl';
import {inject} from 'di';
import Metadata from 'metadata';

/**
 * Internal class for portal access checking. Can be extended to customize access checking
 * for a specific scope.
 */
class AclPortal extends Acl {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /** @inheritDoc */
    checkScope(data, action, precise, entityAccessData) {
        entityAccessData = entityAccessData || {};

        const inAccount = entityAccessData.inAccount;
        const isOwnContact = entityAccessData.isOwnContact;
        const isOwner = entityAccessData.isOwner;

        if (this.getUser().isAdmin()) {
            return true;
        }

        if (data === false) {
            return false;
        }

        if (data === true) {
            return true;
        }

        if (typeof data === 'string') {
            return true;
        }

        if (data === null) {
            return false;
        }

        action = action || null;

        if (action === null) {
            return true;
        }

        if (!(action in data)) {
            return false;
        }

        const value = data[action];

        if (value === 'all') {
            return true;
        }

        if (value === 'yes') {
            return true;
        }

        if (value === 'no') {
            return false;
        }

        if (typeof isOwner === 'undefined') {
            return true;
        }

        if (isOwner && (value === 'own' || value === 'account' || value === 'contact')) {
            return true;
        }

        let result = false;

        if (value === 'account') {
            if (inAccount === null) {
                if (!precise) {
                    return true;
                }

                result = null;
            } else if (inAccount) {
                return true;
            }

            if (isOwnContact === null) {
                if (!precise) {
                    return true;
                }

                result = null;
            } else if (isOwnContact) {
                return true;
            }
        }

        if (value === 'contact') {
            if (isOwnContact === null) {
                if (!precise) {
                    return true;
                }

                result = null;
            } else if (isOwnContact) {
                return true;
            }
        }

        if (isOwner === null) {
            if (!precise) {
                return true;
            }

            result = null;
        }

        return result;
    }

    /** @inheritDoc */
    checkModel(model, data, action, precise) {
        if (this.getUser().isAdmin()) {
            return true;
        }

        const entityAccessData = {
            isOwner: this.checkIsOwner(model),
            inAccount: this.checkInAccount(model),
            isOwnContact: this.checkIsOwnContact(model),
        };

        return this.checkScope(data, action, precise, entityAccessData);
    }

    /** @inheritDoc */
    checkIsOwner(model) {
        if (
            model.hasField('createdBy') &&
            this.getUser().id === model.get('createdById')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user in an account of a model.
     *
     * @param {import('model').default} model A model.
     * @returns {boolean|null} True if in an account, null if not clear.
     */
    checkInAccount(model) {
        const accountsIds = this.getUser().getLinkMultipleIdList('accounts');

        if (!accountsIds.length) {
            return false;
        }

        const link = this.metadata.get(`aclDefs.${model.entityType}.accountLink`);

        if (link) {
            const linkType = model.getLinkType(link);

            if (linkType === 'belongsTo' || linkType === 'hasOne') {
                const idAttribute = link + 'Id';

                if (!model.has(idAttribute)) {
                    return null;
                }

                const id = model.get(idAttribute);

                if (!id) {
                    return false;
                }

                return accountsIds.includes(id);
            }

            if (linkType === 'belongsToParent') {
                const idAttribute = link + 'Id';
                const typeAttribute = link + 'Type';

                if (!model.has(idAttribute) || !model.has(typeAttribute)) {
                    return null;
                }

                const id = model.get(idAttribute);

                if (model.get(typeAttribute) !== 'Account' || !id) {
                    return false;
                }

                return accountsIds.includes(id);
            }

            if (linkType === 'hasMany') {
                if (!model.hasField(link) || model.getFieldType(link) !== 'linkMultiple') {
                    return true;
                }

                if (!model.has(link + 'Ids')) {
                    return null;
                }

                const ids = model.getLinkMultipleIdList(link);

                for (const id of ids) {
                    if (accountsIds.includes(id)) {
                        return true;
                    }
                }

                return false;
            }

            return false;
        }

        if (
            model.hasField('account') &&
            model.get('accountId') &&
            accountsIds.includes(model.get('accountId'))
        ) {
            return true;
        }

        let result = false;

        if (model.hasField('accounts') && model.hasLink('accounts')) {
            if (!model.has('accountsIds')) {
                result = null;
            }

            (model.getLinkMultipleIdList('accounts')).forEach(id => {
                if (accountsIds.includes(id)) {
                    result = true;
                }
            });
        }

        if (
            model.hasField('parent') &&
            model.hasLink('parent') &&
            model.get('parentType') === 'Account' &&
            accountsIds.includes(model.get('parentId'))
        ) {
            return true;
        }

        if (result === false) {
            if (!model.hasField('accounts') && model.hasLink('accounts')) {
                return true;
            }
        }

        return result;
    }

    /**
     * Check if a user is a contact-owner to a model.
     *
     * @param {module:model} model A model.
     * @returns {boolean|null} True if in a contact-owner, null if not clear.
     */
    checkIsOwnContact(model) {
        const contactId = this.getUser().get('contactId');

        if (!contactId) {
            return false;
        }

        const link = this.metadata.get(`aclDefs.${model.entityType}.contactLink`);

        if (link) {
            const linkType = model.getLinkType(link);

            if (linkType === 'belongsTo' || linkType === 'hasOne') {
                const idAttribute = link + 'Id';

                if (!model.has(idAttribute)) {
                    return null;
                }

                return model.get(idAttribute) === contactId;
            }

            if (linkType === 'belongsToParent') {
                const idAttribute = link + 'Id';
                const typeAttribute = link + 'Type';

                if (!model.has(idAttribute) || !model.has(typeAttribute)) {
                    return null;
                }

                if (model.get(typeAttribute) !== 'Contact') {
                    return false;
                }

                return model.get(idAttribute) === contactId;
            }

            if (linkType === 'hasMany') {
                if (!model.hasField(link) || model.getFieldType(link) !== 'linkMultiple') {
                    return true;
                }

                if (!model.has(link + 'Ids')) {
                    return null;
                }

                const ids = model.getLinkMultipleIdList(link);

                return ids.includes(contactId);
            }

            return false;
        }

        if (model.hasField('contact')) {
            if (model.get('contactId')) {
                if (contactId === model.get('contactId')) {
                    return true;
                }
            }
        }

        let result = false;

        if (model.hasField('contacts') && model.hasLink('contacts')) {
            if (!model.has('contactsIds')) {
                result = null;
            }

            (model.getLinkMultipleIdList('contacts')).forEach(id => {
                if (contactId === id) {
                    result = true;
                }
            });
        }

        if (model.hasField('parent') && model.hasLink('parent')) {
            if (model.get('parentType') === 'Contact' && model.get('parentId') === contactId) {
                return true;
            }
        }

        if (result === false) {
            if (!model.hasField('contacts') && model.hasLink('contacts')) {
                return true;
            }
        }

        return result;
    }
}

export default AclPortal;
