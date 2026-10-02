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

import SettingsEditRecordView from 'views/settings/record/edit';

export default class extends SettingsEditRecordView {

    layoutName = 'userInterface'

    saveAndContinueEditingAction = false

    setup() {
        super.setup();

        this.controlColorsField();
        this.listenTo(this.model, 'change:scopeColorsDisabled', () => this.controlColorsField());

        this.on('save', initialAttributes => {
            if (
                this.model.get('theme') !== initialAttributes.theme ||
                (this.model.get('themeParams').navbar || {}) !== (initialAttributes.themeParams).navbar
            ) {
                this.setConfirmLeaveOut(false);

                window.location.reload();
            }
        });
    }

    controlColorsField() {
        if (this.model.get('scopeColorsDisabled')) {
            this.hideField('tabColorsDisabled');
        } else {
            this.showField('tabColorsDisabled');
        }
    }
}
