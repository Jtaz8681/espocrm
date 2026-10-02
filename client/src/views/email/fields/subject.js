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

class EmailSubjectFieldView extends VarcharFieldView {

    listLinkTemplate = 'email/fields/subject/list-link'

    data() {
        const data = super.data();

        data.isRead = (this.model.get('sentById') === this.getUser().id) || this.model.get('isRead');
        data.isImportant = this.model.has('isImportant') && this.model.get('isImportant');
        data.hasAttachment = this.model.has('hasAttachment') && this.model.get('hasAttachment');
        data.isReplied = this.model.has('isReplied') && this.model.get('isReplied');
        data.isAutoReply = this.model.has('isAutoReply') && this.model.attributes.isAutoReply;

        data.hasIcon = data.hasAttachment || data.isAutoReply;

        if (data.hasIcon) {
            data.iconCount = 1;

            if (data.hasAttachment && data.isAutoReply) {
                data.iconCount = 2;
            }
        }

        data.inTrash = this.model.attributes.groupFolderId ?
            this.model.attributes.groupStatusFolder === 'Trash' :
            this.model.attributes.inTrash;

        data.inArchive = this.model.attributes.groupFolderId ?
            this.model.attributes.groupStatusFolder === 'Archive' :
            this.model.attributes.inArchive;

        data.style = null;

        if (data.isImportant) {
            data.style = 'warning';
        } else if (data.inTrash) {
            data.style = 'muted';
        } else if (data.inArchive) {
            data.style = 'info';
        }

        if (!data.isRead && !this.model.has('isRead')) {
            data.isRead = true;
        }

        if (!data.isNotEmpty) {
            if (
                this.model.get('name') !== null &&
                this.model.get('name') !== '' &&
                this.model.has('name')
            ) {
                data.isNotEmpty = true;
            }
        }

        return data;
    }

    getValueForDisplay() {
        return this.model.get('name');
    }

    getAttributeList() {
        return [
            'name',
            'subject',
            'isRead',
            'isImportant',
            'hasAttachment',
            'inTrash',
            'groupStatusFolder',
            'isAutoReply',
        ];
    }

    setup() {
        super.setup();

        this.events['click [data-action="showAttachments"]'] = e => {
            e.stopPropagation();

            this.showAttachments();
        }

        this.listenTo(this.model, 'change:isRead change:isImportant change:groupStatusFolder', () => {
            if (this.mode === this.MODE_LIST || this.mode === this.MODE_LIST_LINK) {
                this.reRender();
            }
        });
    }

    fetch() {
        const data = super.fetch();

        data.name = data.subject;

        return data;
    }

    showAttachments() {
        Espo.Ui.notifyWait();

        this.createView('dialog', 'views/email/modals/attachments', {model: this.model})
            .then(view => {
                view.render();

                Espo.Ui.notify(false);
            });
    }
}

export default EmailSubjectFieldView;
