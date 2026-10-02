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

class GlobalSearchNameFieldView extends BaseFieldView {

    listTemplate = 'global-search/name-field'

    data() {
        const scope = this.model.attributes._scope;

        return {
            scope: scope,
            name: this.model.attributes.name || this.translate('None'),
            id: this.model.id,
            iconHtml: this.getHelper().getScopeColorIconHtml(scope),
        };
    }

    setup() {
        this.addHandler('auxclick', 'a[href]:not([role="button"])', (/** MouseEvent */e) => {
            if (!this.isReadMode()) {
                return;
            }

            const isCombination = e.button === 1 && (e.ctrlKey || e.metaKey);

            if (!isCombination) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            this.quickView();
        });
    }

    quickView() {
        const helper = new RecordModal();

        helper.showDetail(this, {
            id: this.model.id,
            entityType: this.model.attributes._scope,
        });
    }
}

export default GlobalSearchNameFieldView;
