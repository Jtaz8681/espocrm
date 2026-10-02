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

import EnumFieldView from 'views/fields/enum';

class EmailFolderDashletFieldView extends EnumFieldView {

    /** @type {{id: string, name: string}[]} */
    folderDataList

    setup() {
        super.setup();

        let userId = this.dataObject.userId ?? this.getUser().id;

        this.wait(
            Espo.Ajax.getRequest('EmailFolder/action/listAll', {userId: userId})
                .then(data => this.folderDataList = data.list)
                .then(() => this.setupOptions())
        );

        this.setupOptions();
    }

    setupOptions() {
        if (!this.folderDataList) {
            return;
        }

        this.params.options = this.folderDataList
            .map(item => item.id)
            .filter(item => item !== 'inbox' && item !== 'trash');

        this.params.options.unshift('');

        this.translatedOptions = {'': this.translate('inbox', 'presetFilters', 'Email')};

        this.folderDataList.forEach(item => {
            this.translatedOptions[item.id] = item.name;
        });
    }
}

export default EmailFolderDashletFieldView;
