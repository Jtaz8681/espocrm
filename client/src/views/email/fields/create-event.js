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

import BaseFieldView from 'views/fields/base';
import RecordModal from 'helpers/record-modal';

export default class extends BaseFieldView {

    detailTemplate = 'email/fields/create-event/detail'

    eventEntityType = 'Meeting'

    getAttributeList() {
        return [
            'icsEventData',
            'createdEventId',
        ];
    }

    setup() {
        super.setup();

        this.addActionHandler('createEvent', () => this.createEvent());
    }

    createEvent() {
        const eventData = this.model.get('icsEventData') || {};

        const attributes = Espo.Utils.cloneDeep(eventData.valueMap || {});

        attributes.parentId = this.model.get('parentId');
        attributes.parentType = this.model.get('parentType');
        attributes.parentName = this.model.get('parentName');

        this.addFromAddressToAttributes(attributes);

        const helper = new RecordModal();

        helper.showCreate(this, {
            entityType: this.eventEntityType,
            attributes: attributes,
            afterSave: async () => {
                await this.model.fetch();

                Espo.Ui.success(this.translate('Done'))
            },
        });
    }

    /**
     * @private
     * @param {Record} attributes
     */
    addFromAddressToAttributes(attributes) {
        const fromAddress = this.model.get('from');
        const idHash = this.model.get('idHash') || {};
        const typeHash = this.model.get('typeHash') || {};
        const nameHash = this.model.get('nameHash') || {};

        let fromId = null;
        let fromType = null;
        let fromName = null;

        if (!fromAddress) {
            return;
        }

        fromId = idHash[fromAddress] || null;
        fromType = typeHash[fromAddress] || null;
        fromName = nameHash[fromAddress] || null;

        const attendeeLink = this.getAttendeeLink(fromType);

        if (!attendeeLink) {
            return;
        }

        attributes[attendeeLink + 'Ids'] = attributes[attendeeLink + 'Ids'] || [];
        attributes[attendeeLink + 'Names'] = attributes[attendeeLink + 'Names'] || {};

        if (~attributes[attendeeLink + 'Ids'].indexOf(fromId)) {
            return;
        }

        attributes[attendeeLink + 'Ids'].push(fromId);
        attributes[attendeeLink + 'Names'][fromId] = fromName;
    }

    /**
     * @private
     * @param {string} entityType
     * @return {null|string}
     */
    getAttendeeLink(entityType) {
        if (entityType === 'User') {
            return 'users';
        }

        if (entityType === 'Contact') {
            return 'contacts';
        }

        if (entityType === 'Lead') {
            return 'leads';
        }

        return null;
    }
}
