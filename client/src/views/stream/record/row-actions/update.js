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

import StreamDefaultNoteRowActionsView from 'views/stream/record/row-actions/default';

class StreamUpdateNoteRowActionsView extends StreamDefaultNoteRowActionsView {

    setup() {
        super.setup();

        this.addActionHandler('restore', () => this.actionRestore());
    }

    getActionList() {
        const list = super.getActionList();

        if (this.hasRestore()) {
            list.unshift({
                label: 'Restore',
                data: {id: this.model.id},
                action: 'restore',
            });
        }

        return list;
    }

    hasRestore() {
        if (this.options.listType !== 'listAuditLog') {
            return false;
        }

        const entityType = this.model.get('parentType');

        if (this.getMetadata().get(`clientDefs.${entityType}.editDisabled`)) {
            return false;
        }

        if (!this.getAcl().checkScope(entityType, 'edit')) {
            return false;
        }

        const fieldList = this.getFieldList();

        if (!fieldList.length) {
            return false;
        }

        for (const field of fieldList) {
            if (!this.getAcl().checkField(entityType, field, 'edit')) {
                return false;
            }
        }

        return true;
    }

    async actionRestore() {
        await this.confirm({
            message: this.translate('confirmRestoreFromAudit', 'messages'),
            confirmText: this.translate('Proceed'),
        });

        const entityType = this.model.get('parentType');
        const entityId = this.model.get('parentId');

        this.getRouter().dispatch(entityType, 'edit', {
            id: entityId,
            attributes: this.getPreviousAttributes(),
            highlightFieldList: this.getFieldList(),
        });

        this.getRouter().navigate(`#${entityType}/edit/${entityId}`, {trigger: false});
    }

    /**
     * @return {string[]}
     */
    getFieldList() {
        const data = /** @type {Record} */ this.model.get('data') || {};

        return /** @type {string[]} */data.fields || [];
    }

    /**
     * @return {Record}
     */
    getPreviousAttributes() {
        const data = /** @type {Record} */ this.model.get('data') || {};
        const attributes = /** @type {Record} */data.attributes || {};

        return attributes.was || {};
    }
}

export default StreamUpdateNoteRowActionsView;
