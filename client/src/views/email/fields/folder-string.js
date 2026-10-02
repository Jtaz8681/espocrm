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

class EmailFolderStringFieldView extends BaseFieldView {

    // language=Handlebars
    detailTemplateContent = `
        {{#if valueIsSet}}
            {{#if value}}
                {{#if isList}}
                    {{#each value}}
                        <div class="multi-enum-item-container">{{this}}</div>
                    {{/each}}
                {{else}}
                    {{value}}
                {{/if}}

            {{else}}
                <span class="none-value">{{translate 'None'}}</span>
            {{/if}}
        {{else}}
            <span class="loading-value"></span>
        {{/if}}
    `

    // noinspection JSCheckFunctionSignatures
    data() {
        if (!this.model.has('folderId')) {
            return {valueIsSet: false};
        }

        const value = this.getFolderString();

        return {
            valueIsSet: true,
            value: this.getFolderString(),
            isList: Array.isArray(value),
        }
    }

    getAttributeList() {
        return [
            'isUsers',
            'folderId',
            'folderName',
            'groupFolderId',
            'groupFolderName',
            'inArchive',
            'inTrash',
            'isUsersSent',
            'groupStatusFolder',
        ];
    }

    /**
     * @return {string|string[]}
     */
    getFolderString() {
        if (this.model.attributes.groupFolderName) {
            let string = this.translate('group', 'strings', 'Email') + ' · ' + this.model.attributes.groupFolderName;

            if (this.model.attributes.groupStatusFolder === 'Archive') {
                string += ' · ' + this.translate('archive', 'presetFilters', 'Email');
            } else if (this.model.attributes.groupStatusFolder === 'Trash') {
                string += ' · ' + this.translate('trash', 'presetFilters', 'Email');
            }

            if (this.model.attributes.isUsersSent) {
                return [
                    string,
                    this.translate('sent', 'presetFilters', 'Email'),
                ];
            }

            return string;
        }

        let string;

        if (this.model.attributes.inTrash) {
            string = this.translate('trash', 'presetFilters', 'Email');
        }

        if (this.model.attributes.inArchive) {
            string = this.translate('archive', 'presetFilters', 'Email');
        }

        if (this.model.attributes.folderName && this.model.attributes.folderId) {
            string = this.model.attributes.folderName;
        }

        if (string && this.model.attributes.isUsersSent) {
            return [
                string,
                this.translate('sent', 'presetFilters', 'Email'),
            ];
        }

        if (this.model.attributes.isUsersSent) {
            return this.translate('sent', 'presetFilters', 'Email');
        }

        if (this.model.attributes.createdById === this.getUser().id && this.model.attributes.status === 'Draft') {
            return this.translate('drafts', 'presetFilters', 'Email');
        }

        if (string) {
            return string;
        }

        if (this.model.attributes.isUsers) {
            return this.translate('inbox', 'presetFilters', 'Email');
        }

        return undefined;
    }
}

export default EmailFolderStringFieldView;
